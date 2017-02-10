<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\Exception\DomainEventHandlerFailedMessage;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;

class DomainEventConsumer implements QueueMessageConsumer, MessageReceiver
{
    private $maxNumberOfMessagesToProcess = 200;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var DomainEventHandlerLocator
     */
    private $handlerLocator;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Queue $queue, DomainEventHandlerLocator $locator, Logger $logger)
    {
        $this->queue = $queue;
        $this->handlerLocator = $locator;
        $this->logger = $logger;
    }

    public function process()
    {
        $this->processNumberOfMessages($this->maxNumberOfMessagesToProcess);
    }

    public function processAll()
    {
        if (($n = $this->queue->count()) > 0) {
            $this->processNumberOfMessages($n);
        }
    }

    private function processNumberOfMessages(int $numberOfMessagesToProcess)
    {
        try {
            $messageReceiver = $this;
            $this->queue->consume($messageReceiver, $numberOfMessagesToProcess);
        } catch (\Exception $e) {
            $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
        }
    }

    public function receive(Message $message)
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($message);
            $domainEventHandler->process($message);
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($message, $e));
        }
    }
}
