<?php

namespace Pim\Bundle\VersioningBundle\Purger;

/**
 * Purge versions according to registered voters
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionPurgerInterface
{
    /**
     * Purge the versions
     *
     * @param $resourceName
     * @param array $options
     */
    public function purge($resourceName, array $options);


    /**
     * Register a voter
     *
     * @param VoterInterface $voter
     */
    public function addVoter(PurgeVoterInterface $voter);
}
