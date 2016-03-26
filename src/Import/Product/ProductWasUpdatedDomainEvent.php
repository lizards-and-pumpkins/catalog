<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;

class ProductWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var Product
     */
    private $product;

    public function __construct(ProductId $productId, Product $product)
    {
        $this->productId = $productId;
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
