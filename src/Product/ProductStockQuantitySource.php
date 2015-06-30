<?php

namespace Brera\Product;

class ProductStockQuantitySource
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
     * @var int
     */
    private $quantity;

    /**
     * @param Sku $sku
     * @param string[] $contextData
     * @param int $quantity
     */
    public function __construct(Sku $sku, array $contextData, $quantity)
    {
        $this->sku = $sku;
        $this->contextData = $contextData;
        $this->quantity = $quantity;
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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}
