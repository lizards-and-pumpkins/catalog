<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\ProjectionSourceData;

class ProductStockQuantitySource implements ProjectionSourceData
{
    /**
     * @var Sku
     */
    private $sku;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @var Quantity
     */
    private $stock;

    /**
     * @param Sku $sku
     * @param string[] $contextData
     * @param Quantity $stock
     */
    public function __construct(Sku $sku, array $contextData, Quantity $stock)
    {
        $this->sku = $sku;
        $this->contextData = $contextData;
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
     * @return string[]
     */
    public function getContextData()
    {
        return $this->contextData;
    }

    /**
     * @return Quantity
     */
    public function getStock()
    {
        return $this->stock;
    }
}
