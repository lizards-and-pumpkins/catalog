<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductDTO;

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
     * @param ProductDTO $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(ProductDTO $product, AttributeCode $attributeCode)
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
     * @param ProductDTO $product
     * @param AttributeCode $attributeCode
     * @return bool
     */
    private function useSpecialPriceInsteadOfPrice(ProductDTO $product, AttributeCode $attributeCode)
    {
        return $attributeCode->isEqualTo($this->priceAttribute) && $this->hasSpecialPrice($product);
    }

    /**
     * @param ProductDTO $product
     * @return bool
     */
    private function hasSpecialPrice(ProductDTO $product)
    {
        return $product->hasAttribute((string) $this->specialPriceAttribute);
    }

    /**
     * @param ProductDTO $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    protected function getAttributeValuesFromProduct(ProductDTO $product, AttributeCode $attributeCode)
    {
        return $product->getAllValuesOfAttribute((string) $attributeCode);
    }
}
