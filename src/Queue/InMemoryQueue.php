<?php

namespace Brera\PoC\Queue;

class InMemoryQueue implements \Countable, Queue
{
    /**
     * @var array
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
     * @param mixed $data
     * @throws NotSerializable
     * @return null
     */
    public function add($data)
    {
	    if (!$data instanceof \Serializable) {
		    throw new NotSerializable();
	    }

	    $this->queue[] = serialize($data);
    }

    /**
     * @return mixed
     * @throws \UnderflowException
     */
    public function next()
    {
        if (empty($this->queue)) {
            throw new \UnderflowException('Trying to get next message of an empty queue');
        }

	    $data = array_shift($this->queue);

	    return unserialize($data);
    }
} 
