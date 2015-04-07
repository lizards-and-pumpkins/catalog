<?php

namespace Brera;

use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Queue\InMemory\InMemoryQueue;

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

    /**
     * @return string[]
     */
    public function getSearchableAttributeCodes()
    {
        return ['name', 'category'];
    }
}
