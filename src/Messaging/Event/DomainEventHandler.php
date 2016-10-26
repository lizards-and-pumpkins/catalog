<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

interface DomainEventHandler
{
    /**
     * @return void
     */
    public function process();
}
