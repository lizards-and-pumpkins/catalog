<?php

namespace Brera\Product;

use Brera\Context\Context;

class ProductStockQuantitySource
{
    /**
     * @var Sku
     */
    private $sku;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Quantity
     */
    private $stock;

    public function __construct(Sku $sku, Context $context, Quantity $stock)
    {
        $this->sku = $sku;
        $this->context = $context;
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
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Quantity
     */
    public function getStock()
    {
        return $this->stock;
    }
}
