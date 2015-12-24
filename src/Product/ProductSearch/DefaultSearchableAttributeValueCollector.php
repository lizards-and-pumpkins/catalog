<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;

class DefaultSearchableAttributeValueCollector implements SearchableAttributeValueCollector
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
        return $this->useSpecialPriceInsteadOfPrice($product, $attributeCode) ?
            $this->getAttributeValuesFromProduct($product, $this->specialPriceAttribute) :
            $this->getAttributeValuesFromProduct($product, $attributeCode);
    }

    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return bool
     */
    private function useSpecialPriceInsteadOfPrice(Product $product, AttributeCode $attributeCode)
    {
        return $this->priceAttribute->isEqualTo($attributeCode) && $this->hasSpecialPrice($product);
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
    private function getAttributeValuesFromProduct(Product $product, AttributeCode $attributeCode)
    {
        return array_filter($product->getAllValuesOfAttribute((string) $attributeCode), 'is_scalar');
    }
}
