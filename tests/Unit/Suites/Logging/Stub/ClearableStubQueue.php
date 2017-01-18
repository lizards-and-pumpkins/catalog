<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging\Stub;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubQueue implements Queue, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    public function count() : int
    {
        // Intentionally left empty
    }

    public function add(Message $message)
    {
        // Intentionally left empty
    }

    public function consume(MessageReceiver $messageReceiver, int $numberOfMessagesToConsume)
    {
        // Intentionally left empty
    }
}
