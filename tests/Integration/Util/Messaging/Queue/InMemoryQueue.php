<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Exception\NotSerializableException;
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

    /**
     * @param mixed $data
     */
    public function add($data)
    {
        try {
            $this->queue[] = serialize($data);
        } catch (\Exception $e) {
            throw new NotSerializableException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function next()
    {
        if ([] === $this->queue) {
            throw new \UnderflowException('Trying to get next message of an empty queue');
        }

        $data = array_shift($this->queue);

        return unserialize($data);
    }

    public function clear()
    {
        $this->queue = [];
    }
}
