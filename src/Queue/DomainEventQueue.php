<?php
namespace Brera\PoC;

interface DomainEventQueue
{
    /**
     * @return int
     */
    public function count();

    /**
     * @param DomainEvent $event
     * @return null
     */
    public function add(DomainEvent $event);

    /**
     * @return DomainEvent
     */
    public function next();
}