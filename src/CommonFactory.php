<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;
use Brera\Environment\EnvironmentSourceBuilder;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductSourceBuilder;
use Brera\KeyValue\KeyValueStore;
use Brera\Queue\Queue;
use Brera\KeyValue\DataPoolWriter;
use Brera\KeyValue\KeyValueStoreKeyGenerator;
use Brera\KeyValue\DataPoolReader;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductDetailViewSnippetRenderer;
use Brera\Product\HardcodedProductDetailViewSnippetKeyGenerator;
use Brera\Product\HardcodedProductSnippetRendererCollection;
use Psr\Log\LoggerInterface;

class CommonFactory implements Factory, DomainEventFactory
{
	use FactoryTrait;

	/**
	 * @var KeyValueStore
	 */
	private $keyValueStore;

	/**
	 * @var Queue
	 */
	private $eventQueue;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param ProductImportDomainEvent $event
	 * @return ProductImportDomainEventHandler
	 * @todo: move to catalog factory
	 */
	public function createProductImportDomainEventHandler(ProductImportDomainEvent $event)
	{
		return new ProductImportDomainEventHandler(
			$event,
			$this->getMasterFactory()->createProductSourceBuilder(),
			$this->getMasterFactory()->createEnvironmentSourceBuilder(),
			$this->getMasterFactory()->createProductProjector()
		);
	}

	/**
	 * @param CatalogImportDomainEvent $event
	 * @return CatalogImportDomainEventHandler
	 * @todo: move to catalog factory
	 */
	public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event)
	{
		return new CatalogImportDomainEventHandler($event, $this->getMasterFactory()->getEventQueue());
	}

	/**
	 * @return ProductProjector
	 * @todo: move to catalog factory
	 */
	public function createProductProjector()
	{
		return new ProductProjector(
			$this->createProductSnippetRendererCollection(),
			$this->getMasterFactory()->createDataPoolWriter()
		);
	}

	/**
	 * @return HardcodedProductSnippetRendererCollection
	 * @todo: move to catalog factory
	 */
	public function createProductSnippetRendererCollection()
	{
		$rendererList = [$this->getMasterFactory()->createProductDetailViewSnippetRenderer()];

		return new HardcodedProductSnippetRendererCollection(
			$rendererList, $this->getMasterFactory()->createSnippetResultList()
		);
	}

	/**
	 * @return SnippetResultList
	 */
	public function createSnippetResultList()
	{
		return new SnippetResultList();
	}

	/**
	 * @return ProductDetailViewSnippetRenderer
	 * @todo: move to catalog factory
	 */
	public function createProductDetailViewSnippetRenderer()
	{
		return new ProductDetailViewSnippetRenderer(
			$this->getMasterFactory()->createSnippetResultList(),
			$this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator(),
			$this->getMasterFactory()->createThemeLocator()
		);
	}

	/**
	 * @return HardcodedProductDetailViewSnippetKeyGenerator
	 * @todo: move to catalog factory
	 */
	public function createProductDetailViewSnippetKeyGenerator()
	{
		return new HardcodedProductDetailViewSnippetKeyGenerator();
	}

	/**
	 * @return ProductSourceBuilder
	 * @todo: move to catalog factory
	 */
	public function createProductSourceBuilder()
	{
		return new ProductSourceBuilder();
	}

	/**
	 * @return ThemeLocator
	 */
	public function createThemeLocator()
	{
		return new ThemeLocator();
	}

	/**
	 * @return EnvironmentSourceBuilder
	 */
	public function createEnvironmentSourceBuilder()
	{
		/* TODO: Add mechanism to inject data version number to use */
		$version = DataVersion::fromVersionString('1');

		return new EnvironmentSourceBuilder($version, $this->getMasterFactory()->createEnvironmentBuilder());
	}

	/**
	 * @return EnvironmentBuilder
	 */
	public function createEnvironmentBuilder()
	{
		return new EnvironmentBuilder();
	}

	/**
	 * @return DomainEventHandlerLocator
	 */
	public function createDomainEventHandlerLocator()
	{
		return new DomainEventHandlerLocator($this);
	}

	/**
	 * @return DataPoolWriter
	 */
	public function createDataPoolWriter()
	{
		return new DataPoolWriter($this->getKeyValueStore(), $this->createKeyGenerator());
	}

	/**
	 * @return KeyValueStore
	 */
	private function getKeyValueStore()
	{
		if (null === $this->keyValueStore) {
			$this->keyValueStore = $this->getMasterFactory()->createKeyValueStore();
		}

		return $this->keyValueStore;
	}

	/**
	 * @return KeyValueStoreKeyGenerator
	 */
	private function createKeyGenerator()
	{
		return new KeyValueStoreKeyGenerator();
	}

	/**
	 * @return DomainEventConsumer
	 */
	public function createDomainEventConsumer()
	{
		return new DomainEventConsumer(
			$this->getMasterFactory()->getEventQueue(),
			$this->getMasterFactory()->createDomainEventHandlerLocator(), $this->getLogger()
		);
	}

	/**
	 * @return Queue
	 */
	public function getEventQueue()
	{
		if (null === $this->eventQueue) {
			$this->eventQueue = $this->getMasterFactory()->createEventQueue();
		}

		return $this->eventQueue;
	}

	/**
	 * @return DataPoolReader
	 */
	public function createDataPoolReader()
	{
		return new DataPoolReader($this->getKeyValueStore(), $this->createKeyGenerator());
	}

	/**
	 * @return LoggerInterface
	 */
	private function getLogger()
	{
		if (null === $this->logger) {
			$this->logger = $this->getMasterFactory()->createLogger();
		}

		return $this->logger;
	}
} 
