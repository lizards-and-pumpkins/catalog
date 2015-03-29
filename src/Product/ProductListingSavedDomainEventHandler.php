<?php

namespace Brera\Product;

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
     * @var ProductListingSavedDomainEvent
     */
    private $domainEvent;

    public function __construct(
        ProductListingSavedDomainEvent $domainEvent,
        ProductListingSourceBuilder $productListingSourceBuilder,
        ProductListingProjector $projector
    )
    {
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
