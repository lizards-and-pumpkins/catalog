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
    public function getAttributeValue($attributeCode)
    {
        try {
            $value = $this->attributeList->getAttribute($attributeCode)->getValue();
        } catch (ProductAttributeNotFoundException $e) {
            /* TODO: Log */
            $value = '';
        }

        return $value;
    }
}
