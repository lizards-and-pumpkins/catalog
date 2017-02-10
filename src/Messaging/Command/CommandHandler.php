<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface CommandHandler
{
    /**
     * @param Message $message
     * @return void
     */
    public function process(Message $message);
}
