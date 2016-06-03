<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface DomainEvent
{
    /**
     * @return Message
     */
    public function toMessage();

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message);
}
