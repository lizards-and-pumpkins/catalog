<?php

namespace Brera;

use Brera\Queue\Queue;
use Psr\Log\LoggerInterface;

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
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param Queue $queue
	 * @param DomainEventHandlerLocator $locator
	 * @param LoggerInterface $logger
	 */
	public function __construct(Queue $queue, DomainEventHandlerLocator $locator, LoggerInterface $logger)
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
				$this->logger->error($e->getMessage(), [new FailedToReadFromDomainEventQueueMessage($e)]);
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
			$this->logger->error($e->getMessage(), [new DomainEventHandlerFailedMessage($domainEvent, $e)]);
		}
	}
} 
