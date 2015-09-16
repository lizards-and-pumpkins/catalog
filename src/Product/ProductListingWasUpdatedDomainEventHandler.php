<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DomainEventHandler;

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
     * @var ProductListingMetaInfoSnippetProjector
     */
    private $projector;

    public function __construct(
        ProductListingWasUpdatedDomainEvent $domainEvent,
        ContextSource $contextSource,
        ProductListingMetaInfoSnippetProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productListingMetaInfo = $this->domainEvent->getProductListingMetaInfo();
        $this->projector->project($productListingMetaInfo, $this->contextSource);
    }
}
