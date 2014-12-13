<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductBuilder;
use Brera\PoC\Product\ProductRepository;
use Brera\PoC\KeyValue\KeyValueStore;
use Brera\PoC\Queue\DomainEventQueue;
use Brera\PoC\Renderer\PoCProductRenderer;
use Brera\PoC\Product\InMemoryProductRepository;
use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\KeyValue\InMemoryKeyValueStore;
use Brera\PoC\KeyValue\KeyValueStoreKeyGenerator;
use Brera\PoC\Queue\InMemoryDomainEventQueue;
use Brera\PoC\KeyValue\DataPoolReader;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TODO: This method can be safely deleted
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

	/**
	 * @param ProductImportDomainEvent $event
	 * @return ProductImportDomainEventHandler
	 */
	public function createProductImportDomainEventHandler(ProductImportDomainEvent $event)
	{
		return new ProductImportDomainEventHandler(
			$event,
			$this->getMasterFactory()->getProductBuilder(),
			$this->getMasterFactory()->createProductProjector()
		);
	}

	/**
	 * @return PoCProductProjector
	 */
	public function createProductProjector()
    {
        return new PoCProductProjector($this->createProductRenderers(), $this->createDataPoolWriter());
    }

	/**
	 * @return ProductBuilder
	 */
	public function getProductBuilder()
	{
		return new ProductBuilder();
	}

    /**
     * @return DomainEventHandlerLocator
     */
    public function createDomainEventHandlerLocator()
    {
        return new DomainEventHandlerLocator($this);
    }

    /**
     * @return PoCProductRenderer[]
     */
    private function createProductRenderers()
    {
	    /* TODO: Read list of renderer classes from config.xml, instantiate them put into an array */

        return array(new PoCProductRenderer());
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
