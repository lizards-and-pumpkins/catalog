<?php


namespace Brera\PoC;


class InMemoryDomainEventQueue implements \Countable, DomainEventQueue
{
    /**
     * @var DomainEvent[]
     */
    private $queue = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->queue);
    }

    /**
     * @param DomainEvent $event
     * @return null
     */
    public function add(DomainEvent $event)
    {
        $this->queue[] = $event;
    }

    /**
     * @return DomainEvent
     * @throws \UnderflowException
     */
    public function next()
    {
        if (empty($this->queue)) {
            throw new \UnderflowException('Trying to get next message of an empty queue');
        }
        return array_shift($this->queue);
    }
} 