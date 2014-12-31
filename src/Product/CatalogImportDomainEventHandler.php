<?php

namespace Brera\PoC\Product;

use Brera\Poc\Queue\Queue;
use Brera\PoC\DomainEventHandler;

class CatalogImportDomainEventHandler implements DomainEventHandler
{
	/**
	 * @var CatalogImportDomainEvent
	 */
	private $event;

	/**
	 * @var ProductBuilder
	 */
	private $productBuilder;

	/**
	 * @var Queue
	 */
	private $eventQueue;

	public function __construct(CatalogImportDomainEvent $event, ProductBuilder $productBuilder, Queue $eventQueue)
	{
		$this->event = $event;
		$this->productBuilder = $productBuilder;
		$this->eventQueue = $eventQueue;
	}

	public function process()
	{
		$xml = $this->event->getXml();
		$productXmlArray = $this->productBuilder->getProductXmlArray($xml);

		foreach ($productXmlArray as $productXml) {
			$this->eventQueue->add(new ProductImportDomainEvent($productXml));
		}
	}
} 
