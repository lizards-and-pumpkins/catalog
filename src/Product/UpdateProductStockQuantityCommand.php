<?php

namespace Brera\Product;

use Brera\Command;

class UpdateProductStockQuantityCommand implements Command
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var ProductStockQuantitySource
     */
    private $productStockQuantitySource;

    public function __construct(ProductId $productId, ProductStockQuantitySource $productStockQuantitySource)
    {
        $this->productId = $productId;
        $this->productStockQuantitySource = $productStockQuantitySource;
    }

    /**
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return ProductStockQuantitySource
     */
    public function getProductStockQuantitySource()
    {
        return $this->productStockQuantitySource;
    }
}
