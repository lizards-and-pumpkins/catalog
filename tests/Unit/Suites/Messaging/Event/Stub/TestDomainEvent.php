<?php

namespace LizardsAndPumpkins\Messaging\Event\Stub;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class TestDomainEvent implements DomainEvent
{
    const CODE = 'test-event';
    
    /**
     * @return Message
     */
    public function toMessage()
    {
        return Message::withCurrentTime(self::CODE, [], []);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        return new self();
    }
}
