<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEventHandler;

class ProductListingWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingWasAddedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ProductListingCriteriaSnippetProjector
     */
    private $projector;

    public function __construct(
        ProductListingWasAddedDomainEvent $domainEvent,
        ProductListingCriteriaSnippetProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->domainEvent->getProductListingCriteria());
    }
}
