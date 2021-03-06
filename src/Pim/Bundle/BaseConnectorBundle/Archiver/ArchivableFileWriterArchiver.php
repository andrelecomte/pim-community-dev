<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Filesystem\ZipFilesystemFactory;
use Pim\Component\Connector\Writer\File\ArchivableWriterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver extends AbstractFilesystemArchiver
{
    /** @var ZipFilesystemFactory */
    protected $factory;

    /** @var string */
    protected $directory;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ZipFilesystemFactory $factory
     * @param Filesystem           $filesystem
     * @param ContainerInterface   $container
     */
    public function __construct(ZipFilesystemFactory $factory, Filesystem $filesystem, ContainerInterface $container)
    {
        $this->factory    = $factory;
        $this->filesystem = $filesystem;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();
            if ($this->isWriterUsable($writer)) {
                $filesystem = $this->getZipFilesystem(
                    $jobExecution,
                    sprintf('%s.zip', pathinfo($writer->getPath(), PATHINFO_FILENAME))
                );

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $filesystem->put($localPath, file_get_contents($fullPath));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'archive';
    }

    /**
     * Get a fresh zip filesystem for the given job execution
     *
     * @param JobExecution $jobExecution
     * @param string       $zipName
     *
     * @return Filesystem
     */
    protected function getZipFilesystem(JobExecution $jobExecution, $zipName)
    {
        $zipPath = strtr(
            $this->getRelativeArchivePath($jobExecution),
            ['%filename%' => $zipName]
        );

        if (!$this->filesystem->has(dirname($zipPath))) {
            $this->filesystem->createDir(dirname($zipPath));
        }

        return $this->factory->createZip(
            $this->filesystem->getAdapter()->getPathPrefix() .
            $zipPath
        );
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isWriterUsable(ItemWriterInterface $writer)
    {
        return $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1;
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isWriterUsable($step->getWriter())) {
                return true;
            }
        }

        return false;
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
