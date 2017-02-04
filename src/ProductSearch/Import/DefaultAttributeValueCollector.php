<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;

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
    public function getValues(Product $product, AttributeCode $attributeCode) : array
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
    private function isSearchableAttributeValue($value) : bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        return true;
    }

    private function useSpecialPriceInsteadOfPrice(Product $product, AttributeCode $attributeCode) : bool
    {
        return $attributeCode->isEqualTo($this->priceAttribute) && $this->hasSpecialPrice($product);
    }

    private function hasSpecialPrice(Product $product) : bool
    {
        return $product->hasAttribute($this->specialPriceAttribute) &&
               $product->getAllValuesOfAttribute((string) $this->specialPriceAttribute) !== [''];
    }

    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    private function getAttributeValuesFromProduct(Product $product, AttributeCode $attributeCode) : array
    {
        return $product->getAllValuesOfAttribute((string) $attributeCode);
    }
}
