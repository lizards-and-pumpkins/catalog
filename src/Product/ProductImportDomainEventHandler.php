<?php

namespace Brera\PoC\Product;

use Brera\PoC\DomainEventHandler;
use Brera\PoC\EnvironmentBuilder;
use Brera\PoC\VersionedEnvironmentBuilder;

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
	 * @var VersionedEnvironmentBuilder
	 */
	private $environmentBuilder;

	public function __construct(
		ProductImportDomainEvent $event, 
		ProductBuilder $productBuilder, 
		EnvironmentBuilder $environmentBuilder, 
		ProductProjector $projector
	)
	{
		$this->event = $event;
		$this->productBuilder = $productBuilder;
		$this->projector = $projector;
		$this->environmentBuilder = $environmentBuilder;
	}

	/**
	 * @return null
	 */
	public function process()
	{
		$xml = $this->event->getXml();
		$product = $this->productBuilder->createProductFromXml($xml);
		$environment = $this->environmentBuilder->createEnvironmentFromXml($xml);
		$this->projector->project($product, $environment);
	}
} 
