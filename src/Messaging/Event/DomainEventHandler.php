<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface DomainEventHandler
{
    /**
     * @param Message $message
     * @return void
     */
    public function process(Message $message);
}
