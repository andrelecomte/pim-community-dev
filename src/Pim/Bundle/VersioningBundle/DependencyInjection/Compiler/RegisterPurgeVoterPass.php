<?php

namespace Pim\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterPurgeVoterPass implements CompilerPassInterface
{
    /** @staticvar string */
    const REGISTRY_ID = 'pim_versioning.purger.version';

    /** @staticvar string */
    const VOTER_TAG_NAME = 'pim_versioning.purger.voter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_ID)) {
            return;
        }

        $service = $container->getDefinition(self::REGISTRY_ID);

        $taggedServices = $container->findTaggedServiceIds(self::VOTER_TAG_NAME);

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('addAdvisor', [new Reference($id)]);
        }
    }
}
