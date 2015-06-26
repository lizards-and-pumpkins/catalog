<?php

namespace Brera\Product;

use Brera\DomainEvent;

class ProductStockUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var Sku
     */
    private $sku;

    /**
     * @var Stock
     */
    private $stock;

    public function __construct(Sku $sku, Stock $stock)
    {
        $this->sku = $sku;
        $this->stock = $stock;
    }

    /**
     * @return Sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return Stock
     */
    public function getStock()
    {
        return $this->stock;
    }
}
