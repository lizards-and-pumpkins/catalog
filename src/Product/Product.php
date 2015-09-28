<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

class Product implements \JsonSerializable
{
    const URL_KEY = 'url_key';
    const ID = 'product_id';
    
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var ProductAttributeListBuilder
     */
    private $attributeList;
    
    /**
     * @var Context
     */
    private $context;

    public function __construct(ProductId $productId, ProductAttributeListBuilder $attributeList, Context $context)
    {
        $this->productId = $productId;
        // todo: verify the context matches the attribute contexts
        $this->attributeList = $attributeList;
        $this->context = $context;
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
     * @return string|ProductAttributeListBuilder
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
     * @return string[]|ProductAttributeListBuilder[]|mixed[]
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
            'attributes' => $this->attributeList,
            'context' => $this->context
        ];
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}
