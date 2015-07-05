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
    private $domainCommandQueue;

    public function __construct(ProductStockQuantityChangedDomainEvent $event, Queue $domainCommandQueue)
    {
        $this->event = $event;
        $this->domainCommandQueue = $domainCommandQueue;
    }

    public function process()
    {
        $payload = $this->event->getPayload();
        $command = new ProjectProductStockQuantitySnippetDomainCommand($payload);

        $this->domainCommandQueue->add($command);
    }
}
