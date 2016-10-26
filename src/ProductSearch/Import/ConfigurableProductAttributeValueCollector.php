<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

class ConfigurableProductAttributeValueCollector extends DefaultAttributeValueCollector
{
    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(Product $product, AttributeCode $attributeCode) : array
    {
        /** @var ConfigurableProduct $product */
        return $this->isConfigurableProduct($product) && $this->isVariationAttribute($product, $attributeCode) ?
            $this->getValuesFromAssociatedProducts($product, $attributeCode) :
            parent::getValues($product, $attributeCode);
    }

    private function isVariationAttribute(ConfigurableProduct $product, AttributeCode $attributeCode) : bool
    {
        foreach ($product->getVariationAttributes() as $variationAttribute) {
            if ($attributeCode->isEqualTo($variationAttribute)) {
                return true;
            }
        }
        return false;
    }

    private function isConfigurableProduct(Product $product) : bool
    {
        return $product instanceof ConfigurableProduct;
    }

    /**
     * @param ConfigurableProduct $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    private function getValuesFromAssociatedProducts(ConfigurableProduct $product, AttributeCode $attributeCode) : array
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
