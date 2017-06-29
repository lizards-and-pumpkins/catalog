<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntegrationTest extends TestCase
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var Queue
     */
    private $eventMessageQueue;

    /**
     * @var Queue
     */
    private $commandMessageQueue;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var UrlKeyStore
     */
    private $urlKeyStore;

    /**
     * @var IntegrationTestFactory
     */
    private $integrationTestFactory;

    final protected function prepareIntegrationTestMasterFactoryForRequest(HttpRequest $request): CatalogMasterFactory
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        $factory->register(new FrontendFactory($request));

        return $factory;
    }

    final protected function prepareIntegrationTestMasterFactory(): CatalogMasterFactory
    {
        return $this->prepareIntegrationTestMasterFactoryExcludingFactories($factoriesToExclude = []);
    }

    /**
     * @param Factory[] $factoriesToExclude
     * @return CatalogMasterFactory
     */
    final protected function prepareIntegrationTestMasterFactoryExcludingFactories(
        array $factoriesToExclude
    ): CatalogMasterFactory {

        $factoriesToRegister = [
            new CommonFactory(),
            new RestApiFactory(),
            new UpdatingProductImportCommandFactory(),
            new NullProductImageImportCommandFactory(),
            new UpdatingProductListingImportCommandFactory(),
        ];
        
        $masterFactory = new CatalogMasterFactory();
        
        every($factoriesToRegister, function (Factory $factoryToRegister) use ($masterFactory, $factoriesToExclude) {
            $this->registerFactoryIfNotExcluded($masterFactory, $factoriesToExclude, $factoryToRegister);
        });

        $this->prepareIntegrationTestFactory($masterFactory);
        
        $this->registerFactoryIfNotExcluded($masterFactory, $factoriesToExclude, new ProductSearchApiFactory());

        return $masterFactory;
    }

    /**
     * @param MasterFactory $masterFactory
     * @param Factory[] $factoriesToExclude
     * @param Factory $factoryToRegister
     */
    private function registerFactoryIfNotExcluded(
        MasterFactory $masterFactory,
        array $factoriesToExclude,
        Factory $factoryToRegister
    ) {
        if (! in_array($factoryToRegister, $factoriesToExclude, false)) {
            $masterFactory->register($factoryToRegister);
        }
    }

    private function prepareIntegrationTestFactory(MasterFactory $masterFactory)
    {
        $this->integrationTestFactory = new IntegrationTestFactory();
        $masterFactory->register($this->integrationTestFactory);
        if ($this->isFirstInstantiationOfFactory()) {
            $this->storeInMemoryObjects($this->integrationTestFactory);
        } else {
            $this->persistInMemoryObjectsOnFactory($this->integrationTestFactory);
        }
    }

    final protected function getIntegrationTestFactory(MasterFactory $masterFactory): IntegrationTestFactory
    {
        if (null === $this->integrationTestFactory) {
            $this->prepareIntegrationTestFactory($masterFactory);
        }

        return $this->integrationTestFactory;
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
            $failMessageString = implode(PHP_EOL, $failMessages);

            $this->fail($failMessageString);
        }
    }

    private function isFirstInstantiationOfFactory(): bool
    {
        return null === $this->keyValueStore;
    }

    private function storeInMemoryObjects(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventMessageQueue = $factory->getEventMessageQueue();
        $this->commandMessageQueue = $factory->getCommandMessageQueue();
        $this->searchEngine = $factory->getSearchEngine();
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function persistInMemoryObjectsOnFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventMessageQueue($this->eventMessageQueue);
        $factory->setCommandMessageQueue($this->commandMessageQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }

    final protected function importCatalogFixture(MasterFactory $factory, string ...$fixtureCatalogFiles)
    {
        /** @var CatalogImport $import */
        $import = $factory->createCatalogImport();
        $dataVersion = DataVersion::fromVersionString('-1');
        if (count($fixtureCatalogFiles) === 0) {
            throw new \RuntimeException('No catalog fixture file specified.');
        }
        every($fixtureCatalogFiles, function (string $fixtureCatalogFile) use ($import, $dataVersion) {
            $import->importFile(__DIR__ . '/../../shared-fixture/' . $fixtureCatalogFile, $dataVersion);
        });

        $this->processAllMessages($factory);
    }

    final protected function processAllMessages(MasterFactory $factory)
    {
        while ($factory->getCommandMessageQueue()->count() > 0 ||
               $factory->getEventMessageQueue()->count() > 0) {
            $factory->createCommandConsumer()->processAll();
            $factory->createDomainEventConsumer()->processAll();
        }
    }
}
