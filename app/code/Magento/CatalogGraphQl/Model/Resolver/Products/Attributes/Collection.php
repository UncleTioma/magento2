<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Attributes;

use GraphQL\Language\AST\FieldNode;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\Attribute;

/**
 * Gather all eav and custom attributes to use in a GraphQL schema for products
 */
class Collection
{
    /**
     * @var AttributeCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AttributeCollection
     */
    private $collection = null;

    /**
     * @param AttributeCollectionFactory $collectionFactory
     */
    public function __construct(AttributeCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return all custom and eav attributes configured for products.
     *
     * @return AttributeCollection
     */
    public function getAttributes() : AttributeCollection
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addFieldToFilter('is_user_defined', '1');
            $this->collection->addFieldToFilter('attribute_code', ['neq' => 'cost']);
        }

        return $this->collection->load();
    }

    /**
     * Find EAV names based on passed in field names from GraphQL request, match to all known EAV attribute codes.
     *
     * @param string[] $fieldNames
     * @return string[]
     */
    public function getRequestAttributes(array $fieldNames) : array
    {
        $attributes = $this->getAttributes();
        $attributeNames = [];
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $attributeNames[] = $attribute->getAttributeCode();
        }

        $matchedAttributes = [];
        foreach ($fieldNames as $name) {
            if (!in_array($name, $attributeNames)) {
                continue;
            }

            $matchedAttributes[] = $name;
        }

        return $matchedAttributes;

        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== 'products') {
                continue;
            }
            foreach ($node->selectionSet->selections as $selection) {
                if ($selection->name->value !== 'items') {
                    continue;
                }

                foreach ($selection->selectionSet->selections as $itemSelections) {
                    if (in_array($itemSelections->name->value, $attributeCodes)) {
                        $collection->addAttributeToSelect($selection->name->value);
                    }
                }
            }
        }
    }
}
