<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductBuilder;

class ProductImportDomainEventHandler implements DomainEventHandler
{
	/**
	 * @var ProductImportDomainEvent
	 */
	private $event;

	/**
	 * @var ProductBuilder
	 */
	private $productBuilder;

	/**
	 * @var ProductProjector
	 */
	private $projector;

	public function __construct(
		ProductImportDomainEvent $event, ProductBuilder $productBuilder, ProductProjector $projector
	)
	{
		$this->event = $event;
		$this->productBuilder = $productBuilder;
		$this->projector = $projector;
	}

	/**
	 * @return null
	 */
	public function process()
	{
		$xml = $this->event->getXml();
		$product = $this->productBuilder->createProductFromXml($xml);
		$this->projector->project($product);
	}
} 
