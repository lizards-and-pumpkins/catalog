<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class LoggingQueueDecorator implements Queue, Clearable
{
    /**
     * @var Queue
     */
    private $component;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $component, Logger $logger)
    {
        $this->component = $component;
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->component->count();
    }

    /**
     * @return bool
     */
    public function isReadyForNext()
    {
        return $this->component->isReadyForNext();
    }

    public function add(Message $message)
    {
        $this->logger->log(new QueueAddLogMessage($message->getName(), $this->component));
        $this->component->add($message);
    }

    /**
     * @return Message
     */
    public function next()
    {
        return $this->component->next();
    }

    public function clear()
    {
        if ($this->component instanceof Clearable) {
            $this->component->clear();
        }
    }
}
