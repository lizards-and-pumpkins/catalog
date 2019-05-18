<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging;

use LizardsAndPumpkins\Messaging\Queue\Message;

interface MessageReceiver
{
    public function receive(Message $message): void;
}
