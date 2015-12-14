<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

class IntegrationTestProductViewLocator implements ProductViewLocator
{
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        return new IntegrationTestProductView($product);
    }
}
