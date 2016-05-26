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
class BaseAdvisor implements AdvisorInterface
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
     * Prevent first and last versions from being purged
     *
     * @param VersionInterface $version
     * @param array $options
     *
     * @return bool
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        if (1 === $version->getVersion()) {
            return false;
        }

        $latestVersion = $this->versionRepository->getNewestLogEntry(
            $version->getResourceName(),
            $version->getResourceId()
        );

        if (null !== $latestVersion && $latestVersion->getId() === $version->getId()) {
            return false;
        }

        return true;
    }
}
