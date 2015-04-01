<?php

namespace Brera;

use Brera\DataPool\KeyValue\File\FileKeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Queue\InMemory\InMemoryQueue;

class SampleFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return FileKeyValueStore
     */
    public function createKeyValueStore()
    {
        $storagePath = '/tmp/brera';
        if (! file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }
        return new FileKeyValueStore($storagePath);
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
