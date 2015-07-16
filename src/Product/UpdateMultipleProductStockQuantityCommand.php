<?php

namespace Brera\Product;

use Brera\Command;

class UpdateMultipleProductStockQuantityCommand implements Command
{
    /**
     * @var ProductStockQuantitySource[]
     */
    private $productStockQuantitySourceArray;

    /**
     * @param ProductStockQuantitySource[] $productStockQuantitySourceArray
     */
    public function __construct(array $productStockQuantitySourceArray)
    {
        $this->productStockQuantitySourceArray = $productStockQuantitySourceArray;
    }

    /**
     * @return ProductStockQuantitySource[]
     */
    public function getProductStockQuantitySourceArray()
    {
        return $this->productStockQuantitySourceArray;
    }
}
