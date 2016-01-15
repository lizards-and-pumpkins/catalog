<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;

abstract class AbstractConfigurableProductView extends AbstractProductView implements CompositeProductView
{
    /**
     * @return ConfigurableProduct
     */
    abstract public function getOriginalProduct();

    /**
     * @return ProductViewLocator
     */
    abstract protected function getProductViewLocator();

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $original = parent::jsonSerialize();

        return array_reduce(array_keys($original), function (array $carry, $key) use ($original) {
            switch ($key) {
                case ConfigurableProduct::SIMPLE_PRODUCT:
                    $result = $this->transformProductJson($original[$key]);
                    break;
                
                case ConfigurableProduct::ASSOCIATED_PRODUCTS:
                    $result = [$key => $this->getAssociatedProducts()];
                    break;
                
                default:
                    $result = [$key => $original[$key]];
                    break;
            }
            return array_merge($carry, $result);
        }, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedProducts()
    {
        return array_map(function (Product $product) {
            return $this->getProductViewLocator()->createForProduct($product);
        }, iterator_to_array($this->getOriginalProduct()->getAssociatedProducts()));
    }

    /**
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes()
    {
        return $this->getOriginalProduct()->getVariationAttributes();
    }
}
