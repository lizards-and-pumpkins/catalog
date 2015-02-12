<?php

namespace Brera;

use Brera\KeyValue\File\FileKeyValueStore;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\SearchEngine\InMemorySearchEngine;

class SampleFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        return new FileKeyValueStore();
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
