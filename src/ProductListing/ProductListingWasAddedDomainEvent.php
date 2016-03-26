<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

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
