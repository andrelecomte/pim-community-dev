<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /** @staticvar string */
    const FIELD_FAMILY = 'family';

    /** @staticvar string */
    const FIELD_GROUPS = 'groups';

    /** @staticvar string */
    const FIELD_CATEGORY = 'categories';

    /** @staticvar string */
    const ITEM_SEPARATOR = ',';

    /** @var string[] */
    protected $supportedFormats = ['csv', 'flat'];

    /** @var array */
    protected $results = [];

    /** @var CollectionFilterInterface */
    protected $filter;

    /**
     * @param CollectionFilterInterface $filter The collection filter
     */
    public function __construct(CollectionFilterInterface $filter = null)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductInterface $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context = $this->resolveContext($context);

        $this->results = $this->serializer->normalize($object->getIdentifier(), $format, $context);

        $this->normalizeFamily($object->getFamily());
        $this->normalizeGroups($object->getGroupCodes());
        $this->normalizeCategories($object->getCategoryCodes());
        $this->normalizeAssociations($object->getAssociations());
        $this->normalizeValues($object, $format, $context);
        $this->normalizeProperties($object);

        return $this->results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize properties
     *
     * @param ProductInterface $product
     */
    protected function normalizeProperties(ProductInterface $product)
    {
        $this->results['enabled'] = (int) $product->isEnabled();
    }

    /**
     * Normalize values
     *
     * @param ProductInterface $product
     * @param string|null      $format
     * @param array            $context
     */
    protected function normalizeValues(ProductInterface $product, $format = null, array $context = [])
    {
        $values = $this->getFilteredValues($product, $context);

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValues = array_replace(
                $normalizedValues,
                $this->serializer->normalize($value, $format, $context)
            );
        }
        ksort($normalizedValues);
        $this->results = array_replace($this->results, $normalizedValues);
    }

    /**
     * Get filtered values
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return ProductValueInterface[]
     */
    protected function getFilteredValues(ProductInterface $product, array $context = [])
    {
        if (null === $this->filter) {
            return $product->getValues();
        }

        $values = $product->getValues();
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection(
                $values,
                $filterType,
                [
                    'channels' => [$context['scopeCode']],
                    'locales'  => $context['localeCodes']
                ]
            );
        }

        return $values;
    }

    /**
     * Normalize the field name for values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';

        if ($value->getAttribute()->isLocalizable()) {
            $suffix = sprintf('-%s', $value->getLocale());
        }
        if ($value->getAttribute()->isScopable()) {
            $suffix .= sprintf('-%s', $value->getScope());
        }

        return $value->getAttribute()->getCode().$suffix;
    }

    /**
     * Normalizes a family
     *
     * @param FamilyInterface $family
     */
    protected function normalizeFamily(FamilyInterface $family = null)
    {
        $this->results[self::FIELD_FAMILY] = $family ? $family->getCode() : '';
    }

    /**
     * Normalizes groups
     *
     * @param GroupInterface[] $groups
     */
    protected function normalizeGroups($groups = [])
    {
        $this->results[self::FIELD_GROUPS] = implode(static::ITEM_SEPARATOR, $groups);
    }

    /**
     * Normalizes categories
     *
     * @param array $categories
     */
    protected function normalizeCategories($categories = [])
    {
        $this->results[self::FIELD_CATEGORY] = implode(static::ITEM_SEPARATOR, $categories);
    }

    /**
     * Normalize associations
     *
     * @param AssociationInterface[] $associations
     */
    protected function normalizeAssociations($associations = [])
    {
        foreach ($associations as $association) {
            $columnPrefix = $association->getAssociationType()->getCode();

            $groups = [];
            foreach ($association->getGroups() as $group) {
                $groups[] = $group->getCode();
            }

            $products = [];
            foreach ($association->getProducts() as $product) {
                $products[] = $product->getIdentifier();
            }

            $this->results[$columnPrefix.'-groups'] = implode(',', $groups);
            $this->results[$columnPrefix.'-products'] = implode(',', $products);
        }
    }

    /**
     * Merge default format option with context
     *
     * @param array $context
     *
     * @return array
     */
    protected function resolveContext(array $context)
    {
        return array_merge(
            [
                'scopeCode'     => null,
                'localeCodes'   => [],
                'metric_format' => 'multiple_fields',
                'filter_types'  => ['pim.transform.product_value.flat']
            ],
            $context
        );
    }
}
