<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Archive files written by job execution to provide them through a download button
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractFilesystemArchiver
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, ContainerInterface $container)
    {
        $this->filesystem = $filesystem;
        $this->container = $container;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();

            if ($this->isUsableWriter($writer)) {
                if ($writer instanceof ArchivableWriterInterface) {
                    $this->doArchive($jobExecution, $writer->getWrittenFiles());
                } else {
                    $this->doArchive($jobExecution, [$writer->getPath() => basename($writer->getPath())]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'output';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isUsableWriter($step->getWriter())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isUsableWriter(ItemWriterInterface $writer)
    {
        $isDeprecatedWriter = ($writer instanceof FileWriter);
        $isNewWriter = ($writer instanceof AbstractFileWriter);

        if (!($isDeprecatedWriter || $isNewWriter)) {
            return false;
        }

        if ($writer instanceof ArchivableWriterInterface) {
            foreach ($writer->getWrittenFiles() as $filePath => $fileName) {
                if (!is_file($filePath)) {
                    return false;
                }
            }
            return true;
        }

        return is_file($writer->getPath());
    }

    /**
     * @param JobExecution $jobExecution
     * @param array        $filesToArchive ['filePath' => 'fileName']
     */
    protected function doArchive(JobExecution $jobExecution, array $filesToArchive)
    {
        foreach ($filesToArchive as $filePath => $fileName) {
            $archivedFilePath = strtr(
                $this->getRelativeArchivePath($jobExecution),
                [
                    '%filename%' => $fileName,
                ]
            );
            $this->filesystem->put($archivedFilePath, file_get_contents($filePath));
        }
    }

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    final protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
