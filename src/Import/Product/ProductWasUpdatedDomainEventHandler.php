<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

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

    public function __construct(Message $message, ProductProjector $projector)
    {
        $this->event = ProductWasUpdatedDomainEvent::fromMessage($message);
        $this->projector = $projector;
    }

    public function process()
    {
        $this->projector->project($this->event->getProduct());
    }
}
