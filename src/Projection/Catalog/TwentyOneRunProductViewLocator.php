<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

class TwentyOneRunProductViewLocator implements ProductViewLocator
{
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        return new TwentyOneRunProductView($product);
    }
}
