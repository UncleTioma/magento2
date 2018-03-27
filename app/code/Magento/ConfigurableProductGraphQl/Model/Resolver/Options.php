<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as Type;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;

/**
 * {@inheritdoc}
 */
class Options implements ResolverInterface
{
    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param OptionCollection $optionCollection
     * @param ValueFactory $valueFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        OptionCollection $optionCollection,
        ValueFactory $valueFactory,
        MetadataPool $metadataPool
    ) {
        $this->optionCollection = $optionCollection;
        $this->valueFactory = $valueFactory;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        if ($value['type_id'] !== Type::TYPE_CODE || !isset($value[$linkField])) {
            return null;
        }

        $this->optionCollection->addProductId((int)$value[$linkField]);

        $result = function () use ($value, $linkField) {
            return $this->optionCollection->getAttributesByProductId((int)$value[$linkField]) ?: [];
        };

        return $this->valueFactory->create($result);
    }
}
