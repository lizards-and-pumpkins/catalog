<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\Product;

abstract class AbstractConfigurableProductView extends AbstractProductView implements CompositeProductView
{
    abstract public function getOriginalProduct() : Product;

    abstract protected function getProductViewLocator() : ProductViewLocator;

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
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
     * ProductView[]
     */
    public function getAssociatedProducts() : array
    {
        return array_map(function (Product $product) {
            return $this->getProductViewLocator()->createForProduct($product);
        }, iterator_to_array($this->getOriginalProduct()->getAssociatedProducts()));
    }

    public function getVariationAttributes() : ProductVariationAttributeList
    {
        return $this->getOriginalProduct()->getVariationAttributes();
    }
}
