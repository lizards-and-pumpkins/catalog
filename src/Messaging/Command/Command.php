<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface Command
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
