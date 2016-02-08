<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

class ProductListingWasAddedDomainEvent implements DomainEvent
{
    /**
     * @var ProductListing
     */
    private $listingCriteria;

    public function __construct(ProductListing $productListing)
    {
        $this->listingCriteria = $productListing;
    }

    /**
     * @return ProductListing
     */
    public function getListingCriteria()
    {
        return $this->listingCriteria;
    }
}
