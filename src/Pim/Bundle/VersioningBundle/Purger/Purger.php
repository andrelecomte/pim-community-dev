<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Purge versions according to registered advisors
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Purger implements PurgerInterface
{
    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var array */
    protected $advisors = [];

    public function __construct(
        VersionRepositoryInterface $versionRepository,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->versionRepository = $versionRepository;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * Purge the versions
     *
     * @param $resourceName
     * @param array $options
     */
    public function purge($resourceName, array $options)
    {
        $versionsCursor = $this->versionRepository->findVersionsByResources($resourceName);
        
        foreach ($versionsCursor as $version) {
            if ($this->isVersionToPurge($version, $options)) {
                $this->versionRepository->purgeVersion($version);
                $this->objectDetacher->detach($version);
            }
        }
    }

    /**
     * Checks if all advisors agree on deleting the version
     *
     * @param VersionInterface $version
     * @param array $options
     *
     * @return bool
     */
    public function isVersionToPurge(VersionInterface $version, array $options = [])
    {
        foreach ($this->advisors as $advisor) {
            if ($advisor->supports($version) && !$advisor->isPurgeable($version, $options)) {

                return false;
            }
        }

        return true;
    }

    /**
     * Register a advisor
     *
     * @param AdvisorInterface $advisor
     */
    public function addAdvisor(AdvisorInterface $advisor)
    {
        $this->advisors[] = $advisor;
    }
}
