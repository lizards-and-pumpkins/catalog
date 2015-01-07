<?php

namespace Brera;

use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductImportDomainEvent;

class DomainEventHandlerLocator
{
	/**
	 * @var IntegrationTestFactory
	 */
	private $factory;

	/**
	 * @param IntegrationTestFactory $factory
	 */
	public function __construct(IntegrationTestFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @param DomainEvent $event
	 * @return DomainEventHandler
	 * @throws UnableToFindDomainEventHandlerException
	 */
	public function getHandlerFor(DomainEvent $event)
	{
		$eventClass = get_class($event);

		switch ($eventClass) {
			case ProductImportDomainEvent::class :
				return $this->factory->createProductImportDomainEventHandler($event);

			case CatalogImportDomainEvent::class :
				return $this->factory->createCatalogImportDomainEventHandler($event);
		}

		throw new UnableToFindDomainEventHandlerException(
			sprintf('Unable to find a handler for %s domain event', $eventClass)
		);
	}
} 
