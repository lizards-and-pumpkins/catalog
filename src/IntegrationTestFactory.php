<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductRepository,
    Brera\PoC\KeyValue\KeyValueStore,
    Brera\PoC\Queue\DomainEventQueue,
    Brera\PoC\Renderer\PoCProductRenderer,
    Brera\PoC\Product\InMemoryProductRepository,
    Brera\PoC\KeyValue\DataPoolWriter,
    Brera\PoC\KeyValue\InMemoryKeyValueStore,
    Brera\PoC\KeyValue\KeyValueStoreKeyGenerator,
    Brera\PoC\Queue\InMemoryDomainEventQueue,
    Brera\PoC\KeyValue\DataPoolReader;

class IntegrationTestFactory implements Factory 
{
    use FactoryTrait;
    
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var DomainEventQueue
     */
    private $eventQueue;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ProductCreatedDomainEvent $event
     * @return ProductCreatedDomainEventHandler
     */
    public function createProductCreatedDomainEventHandler(ProductCreatedDomainEvent $event)
    {
        return new ProductCreatedDomainEventHandler(
            $event,
            $this->getMasterFactory()->getProductRepository(), 
            $this->getMasterFactory()->createProductProjector()
        );
    }
    
    public function createProductProjector()
    {
        return new PoCProductProjector($this->createProductRenderer(), $this->createDataPoolWriter());
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
     * @return InMemoryProductRepository|ProductRepository
     */
    public function getProductRepository()
    {
        if (null === $this->productRepository) {
            $this->productRepository = $this->createProductRepository();
        }
        return $this->productRepository;
    }

    /**
     * @return InMemoryProductRepository
     */
    private function createProductRepository()
    {
        return new InMemoryProductRepository();
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
     * @return DomainEventQueue|InMemoryDomainEventQueue
     */
    public function getEventQueue()
    {
        if (null === $this->eventQueue) {
            $this->eventQueue = $this->createEventQueue();
        }
        return $this->eventQueue;
    }

    /**
     * @return InMemoryDomainEventQueue
     */
    private function createEventQueue()
    {
        return new InMemoryDomainEventQueue();
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
