<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DomainEventHandler;

class ProductStockQuantityWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductStockQuantityWasUpdatedDomainEvent
     */
    private $event;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductStockQuantityProjector
     */
    private $projector;

    public function __construct(
        ProductStockQuantityWasUpdatedDomainEvent $event,
        ContextSource $contextSource,
        ProductStockQuantityProjector $projector
    ) {
        $this->event = $event;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productStockQuantitySource = $this->event->getProductStockQuantitySource();

        $this->projector->project($productStockQuantitySource, $this->contextSource);
    }
}
