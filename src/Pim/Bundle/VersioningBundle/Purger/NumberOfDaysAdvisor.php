<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Component\Versioning\Model\VersionInterface;

/**
 * Checks if
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberOfDaysAdvisor implements AdvisorInterface
{
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
        $limitDate = date_sub(
            new \Datetime('now', new \DateTimeZone('UTC')),
            date_interval_create_from_date_string(sprintf('%s days', $options['days_number']))
        );

        if ($version->getLoggedAt() < $limitDate) {

            return true;
        }

        return false;
    }
}
