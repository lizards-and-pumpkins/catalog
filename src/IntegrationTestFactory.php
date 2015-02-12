<?php

namespace Brera;

use Brera\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\SearchEngine\InMemorySearchEngine;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;
    
    /**
     * @return InMemoryKeyValueStore
     */
    public function createKeyValueStore()
    {
        return new InMemoryKeyValueStore();
    }

    /**
     * @return InMemoryQueue
     */
    public function createEventQueue()
    {
        return new InMemoryQueue();
    }

    /**
     * @return InMemoryLogger
     */
    public function createLogger()
    {
        return new InMemoryLogger();
    }

    /**
     * @return InMemorySearchEngine
     */
    public function createSearchEngine()
    {
        return new InMemorySearchEngine();
    }
}
