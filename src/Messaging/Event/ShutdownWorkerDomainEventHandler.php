<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

class ShutdownWorkerDomainEventHandler implements DomainEventHandler
{
    const MAX_RETRIES = 100;

    /**
     * @var ShutdownWorkerDomainEvent
     */
    private $event;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    public function __construct(Message $message, DomainEventQueue $eventQueue)
    {
        $this->event = ShutdownWorkerDomainEvent::fromMessage($message);
        $this->eventQueue = $eventQueue;
    }

    public function process()
    {
        if ($this->isMatchingCurrentProcess()) {
            shutdown();
        }
        $this->addDomainEventToQueueAgain();
    }

    private function addDomainEventToQueueAgain()
    {
        $retryCount = $this->event->getRetryCount() + 1;
        if ($retryCount <= self::MAX_RETRIES) {
            $this->eventQueue->add(new ShutdownWorkerDomainEvent($this->event->getPid(), $retryCount));
        }
    }

    private function isMatchingCurrentProcess() : bool
    {
        return '*' === $this->event->getPid() || getmypid() == $this->event->getPid();
    }
}
