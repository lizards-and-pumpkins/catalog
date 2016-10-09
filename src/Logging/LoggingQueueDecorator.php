<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class LoggingQueueDecorator implements Queue, Clearable
{
    /**
     * @var Queue
     */
    private $decoratedQueue;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $queueToDecorate, Logger $logger)
    {
        $this->decoratedQueue = $queueToDecorate;
        $this->logger = $logger;
    }

    public function count() : int
    {
        return $this->decoratedQueue->count();
    }

    public function add(Message $message)
    {
        $this->logger->log(new QueueAddLogMessage($message->getName(), $this->decoratedQueue));
        $this->decoratedQueue->add($message);
    }

    public function clear()
    {
        if ($this->decoratedQueue instanceof Clearable) {
            $this->decoratedQueue->clear();
        }
    }

    public function consume(MessageReceiver $messageReceiver, int $maxNumberOfMessagesToConsume)
    {
        $this->decoratedQueue->consume($messageReceiver, $maxNumberOfMessagesToConsume);
    }
}
