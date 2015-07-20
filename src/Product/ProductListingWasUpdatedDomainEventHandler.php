<?php

namespace Brera\Product;

use Brera\DomainEventHandler;

class ProductListingWasUpdatedDomainEventHandler implements DomainEventHandler
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
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    public function __construct(
        ProductListingWasUpdatedDomainEvent $domainEvent,
        ProductListingSourceBuilder $productListingSourceBuilder,
        ProductListingProjector $projector
    ) {
        $this->domainEvent = $domainEvent;
        $this->productListingSourceBuilder = $productListingSourceBuilder;
        $this->projector = $projector;
    }

    public function process()
    {
        $xml = $this->domainEvent->getXml();
        $productListingSource = $this->productListingSourceBuilder->createProductListingSourceFromXml($xml);

        $this->projector->project($productListingSource);
    }
}
