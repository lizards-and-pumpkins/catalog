<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface Command
{
    public function toMessage() : Message;

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message);
}
