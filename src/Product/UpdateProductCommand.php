<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

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
     * @return SimpleProduct
     */
    public function getProduct()
    {
        return $this->product;
    }
}
