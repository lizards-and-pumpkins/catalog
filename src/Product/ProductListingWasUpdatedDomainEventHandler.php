<?php

namespace Brera\Product;

use Brera\DomainEventHandler;

class ProductListingWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ProductListingProjector
     */
    private $projector;

    public function __construct(ProductListingWasUpdatedDomainEvent $domainEvent, ProductListingProjector $projector)
    {
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        $productListingSource = $this->domainEvent->getProductListingSource();
        $this->projector->project($productListingSource);
    }
}
