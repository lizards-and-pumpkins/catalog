<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;

class DefaultAttributeValueCollector implements AttributeValueCollector
{
    /**
     * @var AttributeCode
     */
    private $specialPriceAttribute;

    /**
     * @var AttributeCode
     */
    private $priceAttribute;

    public function __construct()
    {
        $this->specialPriceAttribute = AttributeCode::fromString(PriceSnippetRenderer::SPECIAL_PRICE);
        $this->priceAttribute = AttributeCode::fromString(PriceSnippetRenderer::PRICE);
    }
    
    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(Product $product, AttributeCode $attributeCode)
    {
        $values = $this->useSpecialPriceInsteadOfPrice($product, $attributeCode) ?
            $this->getAttributeValuesFromProduct($product, $this->specialPriceAttribute) :
            $this->getAttributeValuesFromProduct($product, $attributeCode);
        return array_filter($values, [$this, 'isSearchableAttributeValue']);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isSearchableAttributeValue($value)
    {
        if (!is_scalar($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }

    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return bool
     */
    private function useSpecialPriceInsteadOfPrice(Product $product, AttributeCode $attributeCode)
    {
        return $attributeCode->isEqualTo($this->priceAttribute) && $this->hasSpecialPrice($product);
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function hasSpecialPrice(Product $product)
    {
        return $product->hasAttribute((string) $this->specialPriceAttribute);
    }

    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    protected function getAttributeValuesFromProduct(Product $product, AttributeCode $attributeCode)
    {
        return $product->getAllValuesOfAttribute((string) $attributeCode);
    }
}
