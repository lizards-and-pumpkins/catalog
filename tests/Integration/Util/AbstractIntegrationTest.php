<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

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
     * @var UrlKeyStore
     */
    private $urlKeyStore;
    
    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactoryForRequest(HttpRequest $request)
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        $factory->register(new FrontendFactory($request));
        return $factory;
    }
    
    /**
     * @return SampleMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactory()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new UpdatingProductImportCommandFactory());
        $factory->register(new NullProductImageImportCommandFactory());
        $factory->register(new UpdatingProductListingImportCommandFactory());
        $this->registerIntegrationTestFactory($factory);
        return $factory;
    }

    final protected function failIfMessagesWhereLogged(Logger $logger)
    {
        $messages = $logger->getMessages();

        if (count($messages) > 0) {
            $failMessages = array_map(function (LogMessage $logMessage) {
                $messageContext = $logMessage->getContext();
                if (isset($messageContext['exception'])) {
                    /** @var \Exception $exception */
                    $exception = $messageContext['exception'];
                    return (string) $logMessage . ' ' . $exception->getFile() . ':' . $exception->getLine();
                }
                return (string) $logMessage;
            }, $messages);
            $fainMessageString = implode(PHP_EOL, $failMessages);

            $this->fail($fainMessageString);
        }
    }

    /**
     * @param MasterFactory $masterFactory
     * @return IntegrationTestFactory
     */
    private function registerIntegrationTestFactory(MasterFactory $masterFactory)
    {
        $factory = new IntegrationTestFactory($masterFactory);
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
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function persistInMemoryObjectsOnFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventQueue($this->eventQueue);
        $factory->setCommandQueue($this->commandQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }

    final protected function importCatalogFixture(MasterFactory $factory)
    {
        /** @var CatalogImport $import */
        $import = $factory->createCatalogImport();
        $import->importFile(__DIR__ . '/../../shared-fixture/catalog.xml');

        $this->processQueueWhileMessagesPending($factory->getCommandQueue(), $factory->createCommandConsumer());
        $this->processQueueWhileMessagesPending($factory->getEventQueue(), $factory->createDomainEventConsumer());
    }
    
    final protected function processQueueWhileMessagesPending(Queue $queue, QueueMessageConsumer $consumer)
    {
        while ($queue->count()) {
            $consumer->process();
        }
    }
}
