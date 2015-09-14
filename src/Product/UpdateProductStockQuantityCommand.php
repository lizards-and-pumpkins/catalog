<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

class UpdateProductStockQuantityCommand implements Command
{
    /**
     * @var ProductStockQuantitySource
     */
    private $productStockQuantitySource;

    public function __construct(ProductStockQuantitySource $productStockQuantitySource)
    {
        $this->productStockQuantitySource = $productStockQuantitySource;
    }

    /**
     * @return ProductStockQuantitySource
     */
    public function getProductStockQuantitySource()
    {
        return $this->productStockQuantitySource;
    }
}
