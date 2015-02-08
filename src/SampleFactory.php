<?php

namespace Brera;

use Brera\KeyValue\File\FileKeyValueStore;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductBuilder;
use Brera\KeyValue\KeyValueStore;
use Brera\Queue\Queue;
use Brera\KeyValue\DataPoolWriter;
use Brera\KeyValue\KeyValueStoreKeyGenerator;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\KeyValue\DataPoolReader;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductProjector;
use Brera\Product\ProductDetailViewSnippetRenderer;
use Brera\Product\HardcodedProductDetailViewSnippetKeyGenerator;
use Brera\Product\HardcodedProductSnippetRendererCollection;
use Psr\Log\LoggerInterface;

class SampleFactory implements Factory, DomainEventFactory
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
            $this->getMasterFactory()->getEnvironmentSourceBuilder(),
            $this->getMasterFactory()->createProductProjector()
        );
    }

    /**
     * @param CatalogImportDomainEvent $event
     * @return CatalogImportDomainEventHandler
     */
    public function createCatalogImportDomainEventHandler(CatalogImportDomainEvent $event)
    {
        return new CatalogImportDomainEventHandler($event, $this->getMasterFactory()->getEventQueue());
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
     * @return ProductDetailViewSnippetRenderer
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
    public function getEnvironmentSourceBuilder()
    {
        /* TODO: Add mechanism to inject data version number to use */
        $version = DataVersion::fromVersionString('1');

        return new EnvironmentSourceBuilder($version, $this->createEnvironmentBuilder());
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
     * @return FileKeyValueStore|KeyValueStore
     */
    private function getKeyValueStore()
    {
        if (null === $this->keyValueStore) {
            $this->keyValueStore = $this->createKeyValueStore();
        }

        return $this->keyValueStore;
    }

    /**
     * @return FileKeyValueStore
     */
    private function createKeyValueStore()
    {
        return new FileKeyValueStore();
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
