<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue;

class DomainEventQueue
{
    const VERSION_KEY = 'data_version';
    
    /**
     * @var Queue
     */
    private $messageQueue;

    public function __construct(Queue $messageQueue)
    {
        $this->messageQueue = $messageQueue;
    }

    public function add(DomainEvent $event)
    {
        $this->messageQueue->add($event->toMessage());
    }
}
