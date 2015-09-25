<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

class ProductListingWasAddedDomainEvent implements DomainEvent
{
    /**
     * @var ProductListingCriteria
     */
    private $productListingCriteria;

    public function __construct(ProductListingCriteria $productListingCriteria)
    {
        $this->productListingCriteria = $productListingCriteria;
    }

    /**
     * @return ProductListingCriteria
     */
    public function getProductListingCriteria()
    {
        return $this->productListingCriteria;
    }
}
