<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoProductListingWasAddedDomainEventMessage;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
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

    public function __construct(Message $message, ProductListingSnippetProjector $projector)
    {
        $this->domainEvent = ProductListingWasAddedDomainEvent::fromMessage($message);
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->domainEvent->getListingCriteria());
    }
}
