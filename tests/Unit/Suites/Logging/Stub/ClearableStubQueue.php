<?php

namespace LizardsAndPumpkins\Logging\Stub;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubQueue implements Queue, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    /**
     * @return int
     */
    public function count()
    {
        // Intentionally left empty
    }

    /**
     * @return bool
     */
    public function isReadyForNext()
    {
        // Intentionally left empty
    }

    public function add(Message $message)
    {
        // Intentionally left empty
    }

    /**
     * @return Message
     */
    public function next()
    {
        // Intentionally left empty
    }
}
