<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CurrentDataVersionWasSetDomainEventHandler implements DomainEventHandler
{
    /**
     * @var CurrentDataVersionWasSetDomainEvent
     */
    private $event;
    
    public function __construct(Message $message)
    {
        $this->event = CurrentDataVersionWasSetDomainEvent::fromMessage($message);
    }

    public function process()
    {
        
    }
}
