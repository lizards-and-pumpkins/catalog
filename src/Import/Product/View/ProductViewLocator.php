<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Product;

interface ProductViewLocator
{
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product);
}
