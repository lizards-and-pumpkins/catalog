<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector;

class ProductListingWasAddedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingWasAddedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ProductListingSnippetProjector
     */
    private $projector;

    public function __construct(
        ProductListingWasAddedDomainEvent $domainEvent,
        ProductListingSnippetProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->domainEvent->getListingCriteria());
    }
}
