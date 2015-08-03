<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

class ProductListingSavedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductListingProjector
     */
    private $projector;

    /**
     * @var ProductListingSourceBuilder
     */
    private $productListingSourceBuilder;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductListingSavedDomainEvent
     */
    private $domainEvent;

    public function __construct(
        ProductListingSavedDomainEvent $domainEvent,
        ProductListingSourceBuilder $productListingSourceBuilder,
        ProductListingProjector $projector,
        ContextSource $contextSource
    ) {
        $this->domainEvent = $domainEvent;
        $this->productListingSourceBuilder = $productListingSourceBuilder;
        $this->projector = $projector;
        $this->contextSource = $contextSource;
    }

    public function process()
    {
        $xml = $this->domainEvent->getXml();
        $productListingSource = $this->productListingSourceBuilder->createProductListingSourceFromXml($xml);

        $this->projector->project($productListingSource, $this->contextSource);
    }
}
