<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

class ProductWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var SimpleProduct
     */
    private $product;

    public function __construct(ProductId $productId, SimpleProduct $product)
    {
        $this->productId = $productId;
        $this->product = $product;
    }

    /**
     * @return SimpleProduct
     */
    public function getProduct()
    {
        return $this->product;
    }
}
