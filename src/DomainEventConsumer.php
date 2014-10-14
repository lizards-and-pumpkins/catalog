<?php


namespace Brera\PoC;

class DomainEventConsumer
{
    /**
     * @var DomainEventQueue
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

    /**
     * @param DomainEventQueue $queue
     * @param DomainEventHandlerLocator $locator
     * @param Logger $logger
     */
    public function __construct(DomainEventQueue $queue, DomainEventHandlerLocator $locator, Logger $logger)
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
        for ($i = 0; $i < $numberOfMessages; $i++) {
            try {
                $domainEvent = $this->queue->next();
                $this->processDomainEvent($domainEvent);
            } catch (\Exception $e) {
                $this->logger->log(new FailedToReadFromDomainEventQueueMessage($e));
            }
        }
    }

    /**
     * @param DomainEvent $domainEvent
     */
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