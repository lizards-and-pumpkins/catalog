<?php

namespace Brera\PoC;

use Brera\PoC\Product\CatalogImportDomainEvent;
use Brera\PoC\Product\CatalogImportDomainEventHandler;
use Brera\PoC\Product\ProductBuilder;
use Brera\Lib\KeyValue\KeyValueStore;
use Brera\Lib\Queue\Queue;
use Brera\PoC\Renderer\PoCProductRenderer;
use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\Lib\KeyValue\InMemoryKeyValueStore;
use Brera\PoC\KeyValue\KeyValueStoreKeyGenerator;
use Brera\Lib\Queue\InMemoryQueue;
use Brera\PoC\KeyValue\DataPoolReader;
use Brera\PoC\Product\ProductImportDomainEvent;
use Brera\PoC\Product\ProductImportDomainEventHandler;
use Brera\PoC\Product\ProductProjector;
use Brera\PoC\Product\HardcodedProductDetailViewSnippetRenderer;
use Brera\PoC\Product\HardcodedProductDetailViewSnippetKeyGenerator;
use Brera\PoC\Product\HardcodedProductSnippetRendererCollection;
use Psr\Log\LoggerInterface;

class IntegrationTestFactory implements Factory 
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
	 */
	public function createProductImportDomainEventHandler(ProductImportDomainEvent $event)
	{
		return new ProductImportDomainEventHandler(
			$event,
			$this->getMasterFactory()->getProductBuilder(),
            $this->getMasterFactory()->getEnvironmentBuilder(),
			$this->getMasterFactory()->createProductProjector()
		);
	}

	/**
	 * @param CatalogImportDomainEvent $event
	 * @return CatalogImportDomainEventHandler
	 */
	public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event)
	{
		return new CatalogImportDomainEventHandler(
			$event,
			$this->getMasterFactory()->getProductBuilder(),
			$this->getMasterFactory()->getEventQueue()
		);
	}

	/**
	 * @return ProductProjector
	 */
	public function createProductProjector()
    {
        return new ProductProjector($this->createProductSnippetRendererCollection(), $this->createDataPoolWriter());
    }

    /**
     * @return HardcodedProductSnippetRendererCollection
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
     * @return HardcodedProductDetailViewSnippetRenderer
     */
    public function createProductDetailViewSnippetRenderer()
    {
        return new HardcodedProductDetailViewSnippetRenderer(
            $this->getMasterFactory()->createSnippetResultList(),
            $this->getMasterFactory()->createProductDetailViewSnippetKeyGenerator()
        );
    }

    /**
     * @return HardcodedProductDetailViewSnippetKeyGenerator
     */
    public function createProductDetailViewSnippetKeyGenerator()
    {
        return new HardcodedProductDetailViewSnippetKeyGenerator();
    }

	/**
	 * @return ProductBuilder
	 */
	public function getProductBuilder()
	{
		return new ProductBuilder();
	}

    public function getEnvironmentBuilder()
    {
        // todo: add mechanism to inject data version number to use
        $version = DataVersion::fromVersionString('1');
        return new VersionedEnvironmentBuilder($version);
    }

    /**
     * @return DomainEventHandlerLocator
     */
    public function createDomainEventHandlerLocator()
    {
        return new DomainEventHandlerLocator($this);
    }

    /**
     * @return PoCProductRenderer
     */
    private function createProductRenderer()
    {
        return new PoCProductRenderer();
    }

    /**
     * @return DataPoolWriter
     */
    public function createDataPoolWriter()
    {
        return new DataPoolWriter($this->getKeyValueStore(), $this->createKeyGenerator());
    }

    /**
     * @return InMemoryKeyValueStore|KeyValueStore
     */
    private function getKeyValueStore()
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }
        return $this->keyValueStore;
    }

    /**
     * @return InMemoryKeyValueStore
     */
    private function createKeyValueStore()
    {
        return new InMemoryKeyValueStore();
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
     * @return Queue|InMemoryQueue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    /**
     * @return InMemoryQueue
     */
    private function createEventQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return DataPoolReader
     */
    public function createDataPoolReader()
    {
        return new DataPoolReader($this->getKeyValueStore(), $this->createKeyGenerator());
    }

    private function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = $this->createLogger();
        }
        return $this->logger;
    }

    /**
     * @return InMemoryLogger
     */
    private function createLogger()
    {
        return new InMemoryLogger();
    }
} 
