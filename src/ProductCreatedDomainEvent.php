<?php

namespace Brera\PoC;

use Brera\Poc\Product\ProductId;

class ProductCreatedDomainEvent implements DomainEvent
{
    /**
     * @var ProductId
     */
    private $productId;

    function __construct(ProductId $productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->productId;
    }
} 
