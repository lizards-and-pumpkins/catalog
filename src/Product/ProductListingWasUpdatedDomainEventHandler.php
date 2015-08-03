<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

class ProductListingWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductListingProjector
     */
    private $projector;

    public function __construct(
        ProductListingWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        ProductListingProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productListingSource = $this->domainEvent->getProductListingSource();
        $this->projector->project($productListingSource, $this->contextSource);
    }
}
