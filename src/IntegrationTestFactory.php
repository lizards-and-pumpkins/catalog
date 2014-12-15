<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductBuilder;
use Brera\PoC\KeyValue\KeyValueStore;
use Brera\PoC\Queue\DomainEventQueue;
use Brera\PoC\Renderer\PoCProductRenderer;
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
	 * @return ProductProjector
	 */
	public function createProductProjector()
    {
        return new ProductProjector($this->createProductRenderer(), $this->createDataPoolWriter());
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
