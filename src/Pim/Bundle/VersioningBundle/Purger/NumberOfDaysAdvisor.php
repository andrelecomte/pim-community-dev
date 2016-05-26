<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Component\Versioning\Model\VersionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Prevent recent versions from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberOfDaysAdvisor implements AdvisorInterface
{
    /** @staticvar string */
    const DATE_OPERATOR_YOUNGER = '>';

    /** @staticvar string */
    const DATE_OPERATOR_OLDER = '<';

    /**
     * @var OptionsResolver
     */
    protected $optionsResolver;

    public function __construct()
    {
        $this->optionsResolver = $this->createOptionResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(VersionInterface $version)
    {
        return true;
    }

    /**
     * Advises to purge if the version is older than a number of days from now
     *
     * @param VersionInterface $version
     * @param array $options
     *
     * @return bool
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        $options = $this->optionsResolver->resolve($options);

        $limitDate = date_sub(
            new \Datetime('now', new \DateTimeZone('UTC')),
            date_interval_create_from_date_string(sprintf('%s days', $options['days_number']))
        );

        if ((self::DATE_OPERATOR_YOUNGER === $options['days_operator'] && $version->getLoggedAt() > $limitDate)
            || (self::DATE_OPERATOR_OLDER === $options['days_operator'] && $version->getLoggedAt() < $limitDate)
        ) {

            return true;
        }

        return false;
    }

    /**
     * @return OptionsResolver
     */
    protected function createOptionResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined(['days_number', 'days_operator'])
            ->setAllowedTypes('days_number', 'int')
            ->setAllowedValues('days_operator', [self::DATE_OPERATOR_OLDER, self::DATE_OPERATOR_YOUNGER]);

        $resolver->setDefaults(['days_number' => 90]);
        $resolver->setDefaults(['days_operator' => self::DATE_OPERATOR_OLDER]);

        return $resolver;
    }
}
