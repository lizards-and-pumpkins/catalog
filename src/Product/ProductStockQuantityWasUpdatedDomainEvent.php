<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

class ProductStockQuantityWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var ProductStockQuantitySource
     */
    private $productStockQuantitySource;

    public function __construct(ProductId $productId, ProductStockQuantitySource $productStockQuantitySource)
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
