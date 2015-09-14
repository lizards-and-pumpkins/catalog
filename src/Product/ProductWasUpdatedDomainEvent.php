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
     * @var ProductSource
     */
    private $productSource;

    public function __construct(ProductId $productId, ProductSource $productSource)
    {
        $this->productId = $productId;
        $this->productSource = $productSource;
    }

    /**
     * @return ProductSource
     */
    public function getProductSource()
    {
        return $this->productSource;
    }
}
