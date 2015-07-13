<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

class ProductStockQuantityUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductStockQuantityUpdatedDomainEvent
     */
    private $event;

    /**
     * @var ProductStockQuantitySourceBuilder
     */
    private $productStockQuantitySourceBuilder;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductStockQuantityProjector
     */
    private $projector;

    public function __construct(
        ProductStockQuantityUpdatedDomainEvent $event,
        ProductStockQuantitySourceBuilder $productStockQuantitySourceBuilder,
        ContextSource $contextSource,
        ProductStockQuantityProjector $projector
    ) {
        $this->event = $event;
        $this->productStockQuantitySourceBuilder = $productStockQuantitySourceBuilder;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productStockQuantitySource = $this->productStockQuantitySourceBuilder->createFromXml(
            $this->event->getPayload()
        );

        $this->projector->project($productStockQuantitySource, $this->contextSource);
    }
}
