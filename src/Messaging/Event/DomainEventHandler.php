<?php

namespace LizardsAndPumpkins\Messaging\Event;

interface DomainEventHandler
{
    /**
     * @return void
     */
    public function process();
}
