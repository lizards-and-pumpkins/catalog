<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;

class ConfigurableProductSearchableAttributeValueCollector extends DefaultSearchableAttributeValueCollector
{
    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(Product $product, AttributeCode $attributeCode)
    {
        /** @var ConfigurableProduct $product */
        return $this->isConfigurableProduct($product) && $this->isVariationAttribute($product, $attributeCode) ?
            $this->getValuesFromAssociatedProducts($product, $attributeCode) :
            parent::getValues($product, $attributeCode);
    }

    /**
     * @param ConfigurableProduct $product
     * @param AttributeCode $attributeCode
     * @return bool
     */
    private function isVariationAttribute(ConfigurableProduct $product, AttributeCode $attributeCode)
    {
        foreach ($product->getVariationAttributes() as $variationAttribute) {
            if ($attributeCode->isEqualTo($variationAttribute)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isConfigurableProduct(Product $product)
    {
        return $product instanceof ConfigurableProduct;
    }

    /**
     * @param ConfigurableProduct $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    private function getValuesFromAssociatedProducts(ConfigurableProduct $product, AttributeCode $attributeCode)
    {
        return array_reduce(
            $product->getAssociatedProducts()->getProducts(),
            function (array $carry, Product $associatedProduct) use ($attributeCode) {
                return array_merge($carry, $this->getValues($associatedProduct, $attributeCode));
            },
            []
        );
    }
}
