<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Product;

interface ProductViewLocator
{
    public function createForProduct(Product $product) : ProductView;
}
