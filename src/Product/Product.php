<?php

namespace LizardsAndPumpkins\Product;

class Product implements \JsonSerializable
{
    const URL_KEY = 'url_key';
    const ID = 'product_id';
    
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
        
        return isset($attributeValues[0]) ?
            $attributeValues[0] :
            '';
    }

    /**
     * @param string $attributeCode
     * @return string[]|ProductAttributeList[]|mixed[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        if (! $this->attributeList->hasAttribute($attributeCode)) {
            return [];
        }
        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $this->attributeList->getAttributesWithCode($attributeCode));
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'product_id' => (string) $this->productId,
            'attributes' => $this->attributeList
        ];
    }
}
