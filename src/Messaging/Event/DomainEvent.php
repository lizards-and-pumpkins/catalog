<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface DomainEvent
{
    public function toMessage() : Message;

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message);
}
