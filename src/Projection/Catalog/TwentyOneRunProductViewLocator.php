<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;

class TwentyOneRunProductViewLocator implements ProductViewLocator
{
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        if ($product instanceof ConfigurableProduct) {
            return new TwentyOneRunConfigurableProductView($product);
        }

        return new TwentyOneRunSimpleProductView($product);
    }
}
