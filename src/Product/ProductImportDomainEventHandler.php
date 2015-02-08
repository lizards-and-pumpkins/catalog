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
	 * @var ProductBuilder
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
		ProductBuilder $productBuilder,
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
		$environment = $this->environmentSourceBuilder->createFromXml($xml);
		$this->projector->project($product, $environment);
	}
} 
