<?php

namespace Brera\Product;

use Brera\PoCDomParser;
use Brera\Queue\Queue;
use Brera\DomainEventHandler;

class CatalogImportDomainEventHandler implements DomainEventHandler
{
	/**
	 * @var CatalogImportDomainEvent
	 */
	private $event;

	/**
	 * @var Queue
	 */
	private $eventQueue;

	public function __construct(CatalogImportDomainEvent $event, Queue $eventQueue)
	{
		$this->event = $event;
		$this->eventQueue = $eventQueue;
	}

	public function process()
	{
		$xml = $this->event->getXml();

		$parser = new PoCDomParser($xml);

		$productNodes = $parser->getXPathNode('product');
		foreach ($productNodes as $productNode) {
			$productNode = $parser->getDomNodeXml($productNode);
			$this->eventQueue->add(new ProductImportDomainEvent($productNode));
		}
	}
} 
