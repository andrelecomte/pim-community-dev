<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Prevents First and last version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BasePurgeVoter implements PurgeVoterInterface
{
    /**
     * @var VersionRepositoryInterface
     */
    protected $versionRepository;

    public function __construct(VersionRepositoryInterface $versionRepository)
    {
        $this->versionRepository = $versionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(VersionInterface $version)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        $latestVersion = $this->versionRepository->getNewestLogEntry(
            $version->getResourceName(),
            $version->getResourceId()
        );

        $isNotLastVersion = null === $latestVersion || $latestVersion->getId() !== $version->getId();

        if ($version->getVersion() > 1 || $isNotLastVersion) {
            return true;
        }

        return false;
    }
}
