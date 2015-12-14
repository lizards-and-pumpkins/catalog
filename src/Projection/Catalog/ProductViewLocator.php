<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

interface ProductViewLocator
{
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product);
}
