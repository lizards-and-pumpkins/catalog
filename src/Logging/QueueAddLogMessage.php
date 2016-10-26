<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue;

class QueueAddLogMessage implements LogMessage
{
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @param mixed $data
     * @param Queue $queue
     */
    public function __construct($data, Queue $queue)
    {
        $this->data = $data;
        $this->queue = $queue;
    }
    
    public function __toString() : string
    {
        if (is_object($this->data)) {
            $message = sprintf('%s instance added to queue', get_class($this->data));
        } else {
            $message = sprintf('%s added to queue', ucfirst(gettype($this->data)));
        }
        return $message;
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return [
            'queue' => $this->queue,
            'data' => $this->data
        ];
    }

    public function getContextSynopsis() : string
    {
        return sprintf('Queue Class: %s', get_class($this->queue));
    }
}
