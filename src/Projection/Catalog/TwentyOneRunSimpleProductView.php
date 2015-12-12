<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;

class TwentyOneRunSimpleProductView extends AbstractProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductAttributeList
     */
    private $memoizedProductAttributesList;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);

        if (empty($attributeValues)) {
            return '';
        }

        return $attributeValues[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        $attributeList = $this->getAttributes();

        if (!$attributeList->hasAttribute($attributeCode)) {
            return [];
        }

        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $attributeList->getAttributesWithCode($attributeCode));
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($attributeCode)
    {
        return $this->getAttributes()->hasAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        if (null === $this->memoizedProductAttributesList) {
            $originalAttributes = $this->product->getAttributes();
            $this->memoizedProductAttributesList = $this->filterProductAttributeList($originalAttributes);
        }

        return $this->memoizedProductAttributesList;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $productData = $this->product->jsonSerialize();
        $productData['attributes'] = $this->getAttributes();

        return $productData;
    }

    /**
     * @param ProductAttributeList $attributeList
     * @return ProductAttributeList
     */
    private function filterProductAttributeList(ProductAttributeList $attributeList)
    {
        $attributeCodesToBeRemoved = ['price', 'special_price', 'backorders'];

        $filteredAttributes = array_reduce(
            $attributeList->getAllAttributes(),
            function (array $carry, ProductAttribute $attribute) use ($attributeCodesToBeRemoved, $attributeList) {
                if (in_array((string) $attribute->getCode(), $attributeCodesToBeRemoved)) {
                    return $carry;
                }

                if ((string) $attribute->getCode() === 'stock_qty' &&
                    ($attribute->getValue() > self::MAX_PURCHASABLE_QTY ||
                     $this->product->getFirstValueOfAttribute('backorders') === 'true')
                ) {
                    $carry[] = $this->createStockQtyAttributeAtMaximumPurchasableLevel($attribute);
                    return $carry;
                }

                $carry[] = $attribute;
                return $carry;
            },
            []
        );

        return new ProductAttributeList(...$filteredAttributes);
    }

    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    private function createStockQtyAttributeAtMaximumPurchasableLevel(ProductAttribute $attribute)
    {
        return new ProductAttribute('stock_qty', self::MAX_PURCHASABLE_QTY, $attribute->getContextDataSet());
    }
}
