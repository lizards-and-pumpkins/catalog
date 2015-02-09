<?php


namespace Brera;


use Brera\KeyValue\InMemory\InMemoryKeyValueStore;
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
}
