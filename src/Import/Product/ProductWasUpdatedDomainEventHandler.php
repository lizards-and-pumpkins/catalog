<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

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

    public function __construct(ProductWasUpdatedDomainEvent $event, ProductProjector $projector)
    {
        $this->event = $event;
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->event->getProduct());
    }
}
