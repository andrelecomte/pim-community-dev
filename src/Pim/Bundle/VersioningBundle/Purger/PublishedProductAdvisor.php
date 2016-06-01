<?php

namespace PimEnterprise\Bundle\VersioningBundle;

use Akeneo\Component\Versioning\Model\VersionInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

/**
 * Prevents published versions of a product from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishedProductAdvisor implements AdvisorInterfacee
{
    protected $publishedProductRepository;

    public function __construct(PublishedProductRepositoryInterface $publishedProductRepository)
    {
        $this->publishedProductRepository = $publishedProductRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(VersionInterface $version)
    {
        return $version->getResourceName() === 'Product';
    }

    /**
     * {@inheritdoc}
     */
    public function isPurgeable(VersionInterface $version, array $options)
    {
        return null === $this->publishedProductRepository->findOneBy(['versionId' => $version->getResourceId()]);
    }
}
