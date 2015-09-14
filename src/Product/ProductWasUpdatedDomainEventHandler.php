<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DomainEventHandler;

class ProductWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductWasUpdatedDomainEvent
     */
    private $event;

    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        ProductWasUpdatedDomainEvent $event,
        ContextSource $contextSource,
        ProductProjector $projector
    ) {
        $this->event = $event;
        $this->contextSource = $contextSource;
        $this->projector = $projector;
    }

    public function process()
    {
        $productSource = $this->event->getProductSource();
        $this->projector->project($productSource, $this->contextSource);
    }
}
