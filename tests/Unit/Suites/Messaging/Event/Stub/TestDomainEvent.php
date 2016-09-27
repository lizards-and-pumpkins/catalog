<?php

namespace LizardsAndPumpkins\Messaging\Event\Stub;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class TestDomainEvent implements DomainEvent
{
    const CODE = 'test-event';
    
    public function toMessage() : Message
    {
        return Message::withCurrentTime(self::CODE, [], []);
    }

    public static function fromMessage(Message $message) : self
    {
        return new self();
    }
}
