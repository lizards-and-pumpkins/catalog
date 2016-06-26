<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\ProductDTO;

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
            if (ConfigurableProduct::SIMPLE_PRODUCT === $key) {
                return array_merge($carry, $this->transformProductJson($original[$key]));
            }

            if (ConfigurableProduct::ASSOCIATED_PRODUCTS === $key) {
                return array_merge($carry, [$key => $this->getAssociatedProducts()]);
            }

            return array_merge($carry, [$key => $original[$key]]);
        }, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociatedProducts()
    {
        return array_map(function (ProductDTO $product) {
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
