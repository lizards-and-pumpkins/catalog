<?php

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
        $products = $product->getSalableAssociatedProducts()->getProducts();
        
        return array_reduce($products, function (array $carry, Product $associatedProduct) use ($attributeCode) {
            return array_merge($carry, $this->getValues($associatedProduct, $attributeCode));
        }, []);
    }
}
