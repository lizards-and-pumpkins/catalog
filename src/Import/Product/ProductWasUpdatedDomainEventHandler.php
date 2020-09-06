<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class ProductWasUpdatedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductProjector
     */
    private $projector;

    public function __construct(ProductProjector $projector)
    {
        $this->projector = $projector;
    }

    public function process(Message $message): void
    {
        $event = ProductWasUpdatedDomainEvent::fromMessage($message);
        $this->projector->project($event->getProduct());
    }
}
