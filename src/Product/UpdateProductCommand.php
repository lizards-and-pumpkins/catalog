<?php

namespace Brera\Product;

use Brera\Command;

class UpdateProductCommand implements Command
{
    /**
     * @var ProductSource
     */
    private $productSource;

    public function __construct(ProductSource $productSource)
    {
        $this->productSource = $productSource;
    }

    /**
     * @return ProductSource
     */
    public function getProductSource()
    {
        return $this->productSource;
    }
}
