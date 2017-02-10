<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CurrentDataVersionWasSetDomainEventHandler implements DomainEventHandler
{
    public function process(Message $message)
    {
        CurrentDataVersionWasSetDomainEvent::fromMessage($message);
    }
}
