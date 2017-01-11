<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

interface MessageEnvelope
{
    public function toMessage() : Message;

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message);
}
