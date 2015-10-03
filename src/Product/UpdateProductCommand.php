<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Projection\Catalog\Import\ProductBuilder;

class UpdateProductCommand implements Command
{
    /**
     * @var SimpleProduct
     */
    private $product;

    public function __construct(SimpleProduct $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductBuilder
     */
    public function getProduct()
    {
        return $this->product;
    }
}
