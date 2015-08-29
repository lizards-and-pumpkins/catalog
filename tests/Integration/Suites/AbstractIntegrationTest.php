<?php

namespace Brera;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

abstract class AbstractIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventQueue;

    /**
     * @var Queue
     */
    private $commandQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;
    
    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactoryForRequest(HttpRequest $request)
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register($this->createIntegrationTestFactory());
        $factory->register(new FrontendFactory($request));
        return $factory;
    }

    final protected function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (!empty($messages)) {
            $messageString = implode(PHP_EOL, $messages);
            $this->fail($messageString);
        }
    }

    /**
     * @return IntegrationTestFactory
     */
    private function createIntegrationTestFactory()
    {
        $factory = new IntegrationTestFactory();
        if ($this->isFirstInstantiationOfFactory()) {
            $this->storeInMemoryObjects($factory);
        } else {
            $this->persistInMemoryObjectsOnFactory($factory);
        }
        return $factory;
    }

    /**
     * @return bool
     */
    private function isFirstInstantiationOfFactory()
    {
        return null === $this->keyValueStore;
    }
    
    private function storeInMemoryObjects(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventQueue = $factory->getEventQueue();
        $this->commandQueue = $factory->getCommandQueue();
        $this->searchEngine = $factory->getSearchEngine();
    }

    private function persistInMemoryObjectsOnFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventQueue($this->eventQueue);
        $factory->setCommandQueue($this->commandQueue);
        $factory->setSearchEngine($this->searchEngine);
    }
}
