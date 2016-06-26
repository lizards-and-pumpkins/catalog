<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\ProductDTO;

interface ProductViewLocator
{
    /**
     * @param ProductDTO $product
     * @return ProductView
     */
    public function createForProduct(ProductDTO $product);
}
