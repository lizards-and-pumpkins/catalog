<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

class UpdateProductCommand implements Command
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductSource
     */
    public function getProduct()
    {
        return $this->product;
    }
}
