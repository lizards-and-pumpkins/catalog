<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Storage\Clearable;

class InMemoryQueue implements Queue, Clearable
{
    /**
     * @var mixed[]
     */
    private $queue = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->queue);
    }

    /**
     * @return bool
     */
    public function isReadyForNext()
    {
        return $this->count() > 0;
    }

    public function add(Message $message)
    {
        $this->queue[] = $message->serialize();
    }

    /**
     * @return Message
     */
    public function next()
    {
        if ([] === $this->queue) {
            throw new \UnderflowException('Trying to get next message of an empty queue');
        }

        $data = array_shift($this->queue);

        return Message::rehydrate($data);
    }

    public function clear()
    {
        $this->queue = [];
    }
}
