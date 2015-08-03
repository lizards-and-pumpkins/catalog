<?php

namespace Brera;

use Brera\Queue\Queue;
use Brera\Queue\QueueProcessingLimitIsReachedMessage;

class DomainEventConsumer
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

        while ($this->queue->count() > 0 && $numberOfMessagesBeforeReturn-- > 0) {
            try {
                $domainEvent = $this->queue->next();
                $this->processDomainEvent($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
            }
        }

        if ($numberOfMessagesBeforeReturn < 1) {
            $this->logger->log(
                new QueueProcessingLimitIsReachedMessage(__CLASS__, $this->maxNumberOfMessagesToProcess)
            );
        }
    }

    private function processDomainEvent(DomainEvent $domainEvent)
    {
        try {
            $domainEventHandler = $this->handlerLocator->getHandlerFor($domainEvent);
            $domainEventHandler->process();
        } catch (\Exception $e) {
            $this->logger->log(new DomainEventHandlerFailedMessage($domainEvent, $e));
        }
    }
}
