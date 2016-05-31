<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Purge versions according to registered voters
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionPurger implements VersionPurgerInterface
{
    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var array */
    protected $purgerVoters = [];

    public function __construct(
        VersionRepositoryInterface $versionRepository,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->repository = $versionRepository;
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
        // Find all does not scale
        // Works alright in mongo
        // ORM needs a pager implementations

        // $versionsCursor = $this->versionRepository->findAll()
        $versionCursor = [];
        
        foreach ($versionsCursor as $version) {
            if ($this->versionToPurge($version)) {
                // $this->objectDetacher->remove($version);
                $this->objectDetacher->detach($version);
            }
        }
    }

    public function getVersionsCursor($resourceName, $options)
    {

    }

    /**
     * Checks if all voters agree on deleting the version
     *
     * @param VersionInterface $version
     *
     * @return bool
     */
    public function versionToPurge(VersionInterface $version)
    {
        foreach ($this->purgerVoters as $voter) {
            if ($voter->supports($version) && !$voter->isPurgeable($version)) {

                return false;
            }
        }

        return true;
    }

    /**
     * Register a voter
     *
     * @param VoterInterface $voter
     */
    public function addVoter(PurgeVoterInterface $voter)
    {
        $this->purgerVoters[] = $voter;
    }
}
