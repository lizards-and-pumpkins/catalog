<?php


namespace LizardsAndPumpkins\Queue;

use LizardsAndPumpkins\Log\LogMessage;

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
    
    /**
     * @return string
     */
    public function __toString()
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
    public function getContext()
    {
        return [
            'queue' => $this->queue,
            'data' => $this->data
        ];
    }
}
