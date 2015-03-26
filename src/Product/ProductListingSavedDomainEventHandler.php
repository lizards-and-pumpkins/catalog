<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;
use Brera\Projector;

class ProductListingSavedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var Projector
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
        ContextSource $contextSource,
        Projector $projector
    )
    {
        $this->domainEvent = $domainEvent;
        $this->productListingSourceBuilder = $productListingSourceBuilder;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $xml = $this->domainEvent->getXml();
        $productListingSource = $this->productListingSourceBuilder->createProductListingSourceFromXml($xml);

        $this->projector->project($productListingSource, $this->contextSource);
    }
}
