<?php

namespace Brera;

use Brera\Queue\Queue;

class DomainEventConsumer
{
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

    /**
     * @param int $numberOfMessages
     * @return null
     */
    public function process($numberOfMessages)
    {
        for ($i = 0; $i < $numberOfMessages; $i ++) {
            try {
                $domainEvent = $this->queue->next();
                $this->processDomainEvent($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
            }
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
