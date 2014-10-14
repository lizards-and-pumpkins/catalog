<?php


namespace Brera\PoC;


interface DomainEventHandler
{
    /**
     * @return null
     */
    public function process();
} 