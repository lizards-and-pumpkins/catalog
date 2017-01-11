<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;

class EnqueuesMessageEnvelope
{
    /**
     * @var CommandQueue|DomainEventQueue
     */
    private $queue;

    /**
     * @param CommandQueue|DomainEventQueue $queue
     */
    private function __construct($queue)
    {
        $this->queue = $queue;
    }
    
    public static function fromCommandQueue(CommandQueue $commandQueue)
    {
        return new self($commandQueue);
    }

    public static function fromDomainEventQueue(DomainEventQueue $domainEventQueue)
    {
        return new self($domainEventQueue);
    }

    public function add(MessageEnvelope $messageEnvelope)
    {
        $this->queue->add($messageEnvelope);
    }
}
