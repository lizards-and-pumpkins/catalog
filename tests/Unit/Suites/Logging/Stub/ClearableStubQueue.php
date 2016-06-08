<?php

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

    public function count()
    {
        // Intentionally left empty
    }

    public function add(Message $message)
    {
        // Intentionally left empty
    }

    /**
     * @param MessageReceiver $messageReceiver
     * @param int $maxNumberOfMessagesToConsume
     */
    public function consume(MessageReceiver $messageReceiver, $maxNumberOfMessagesToConsume)
    {
        // Intentionally left empty
    }
}
