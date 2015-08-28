<?php

namespace Brera\Product;

class ProductStockQuantitySource
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @var Quantity
     */
    private $stock;

    /**
     * @param ProductId $productId
     * @param string[] $contextData
     * @param Quantity $stock
     */
    public function __construct(ProductId $productId, array $contextData, Quantity $stock)
    {
        $this->productId = $productId;
        $this->contextData = $contextData;
        $this->stock = $stock;
    }

    /**
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->productId;
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
