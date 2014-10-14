<?php


namespace Brera\PoC;


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