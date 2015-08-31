<?php

namespace Brera\Product;

class Product
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    public function __construct(ProductId $productId, ProductAttributeList $attributeList)
    {
        $this->productId = $productId;
        $this->attributeList = $attributeList;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->productId;
    }

    /**
     * @param string $attributeCode
     * @return string|ProductAttributeList
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);

        return $attributeValues[0];
    }

    /**
     * @param string $attributeCode
     * @return string[]|ProductAttributeList[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        try {
            return array_map(function (ProductAttribute $productAttribute) {
                return $productAttribute->getValue();
            }, $this->attributeList->getAttributesWithCode($attributeCode));

        } catch (ProductAttributeNotFoundException $e) {
            /* TODO: Log */
            return [''];
        }
    }
}
