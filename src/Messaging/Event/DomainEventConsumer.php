<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Event\Exception\DomainEventHandlerFailedMessage;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;

class DomainEventConsumer implements QueueMessageConsumer
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
        $numberOfMessagesBeforeReturn = $this->maxNumberOfMessagesToProcess;

        while ($this->queue->isReadyForNext() && $numberOfMessagesBeforeReturn-- > 0) {
            try {
                $domainEvent = $this->queue->next();
                $this->processDomainEvent($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
            }
        }
    }

    private function processDomainEvent(Message $domainEvent)
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($domainEvent);
            $domainEventHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($domainEvent, $e));
        }
    }
}
