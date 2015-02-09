<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Environment\EnvironmentSourceBuilder;

class ProductImportDomainEventHandler implements DomainEventHandler
{
	/**
	 * @var ProductImportDomainEvent
	 */
	private $event;

	/**
	 * @var ProductSourceBuilder
	 */
	private $productBuilder;

	/**
	 * @var ProductProjector
	 */
	private $projector;

	/**
	 * @var EnvironmentSourceBuilder
	 */
	private $environmentSourceBuilder;

	public function __construct(
		ProductImportDomainEvent $event,
		ProductSourceBuilder $productBuilder,
		EnvironmentSourceBuilder $environmentSourceBuilder,
		ProductProjector $projector
	) {
		$this->event = $event;
		$this->productBuilder = $productBuilder;
		$this->projector = $projector;
		$this->environmentSourceBuilder = $environmentSourceBuilder;
	}

	/**
	 * @return null
	 */
	public function process()
	{
		$xml = $this->event->getXml();
		$product = $this->productBuilder->createProductFromXml($xml);
		$environmentSource = $this->environmentSourceBuilder->createFromXml($xml);
		$this->projector->project($product, $environmentSource);
	}
} 
