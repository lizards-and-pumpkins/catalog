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
     * @var Message
     */
    private $domainEvent;

    /**
     * @var ProductListingSnippetProjector
     */
    private $projector;

    public function __construct(Message $domainEvent, ProductListingSnippetProjector $projector)
    {
        if ($domainEvent->getName() !== 'product_listing_was_added_domain_event') {
            $message = sprintf('Expected "product_listing_was_added" domain event, got "%s', $domainEvent->getName());
            throw new NoProductListingWasAddedDomainEventMessage($message);
        }
        $this->domainEvent = $domainEvent;
        $this->projector = $projector;
    }

    public function process()
    {
        // todo: use encapsulate serialization and rehydration in ProductListing not using serialize()
        /** @var ProductListing $productListing */
        $productListing = unserialize(json_decode($this->domainEvent->getPayload(), true)['listing']);
        $this->projector->project($productListing);
    }
}
