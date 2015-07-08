<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Queue\Queue;

class ProductStockQuantityChangedDomainEventHandler implements DomainEventHandler
{
    /**
     * @var ProductStockQuantityChangedDomainEvent
     */
    private $event;

    /**
     * @var Queue
     */
    private $commandQueue;

    public function __construct(ProductStockQuantityChangedDomainEvent $event, Queue $commandQueue)
    {
        $this->event = $event;
        $this->commandQueue = $commandQueue;
    }

    public function process()
    {
        $payload = $this->event->getPayload();
        $command = new ProjectProductStockQuantitySnippetCommand($payload);

        $this->commandQueue->add($command);
    }
}
