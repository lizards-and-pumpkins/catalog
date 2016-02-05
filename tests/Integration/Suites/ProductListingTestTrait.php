<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Queue\Queue;

trait ProductListingTestTrait
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
     * @var SampleMasterFactory
     */
    private $factory;

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableDefaultWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function prepareProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableDefaultWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function registerProductListingSnippetKeyGenerator()
    {
        $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy()->register(
            ProductListingTemplateSnippetRenderer::CODE,
            function () {
                return $this->factory->createProductListingSnippetKeyGenerator();
            }
        );
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function createProductListingRequestHandler()
    {
        return $this->factory->createProductListingRequestHandler();
    }

    /**
     * @param HttpRequest $request
     * @return SampleMasterFactory
     */
    private function createIntegrationTestMasterFactoryForRequest(HttpRequest $request)
    {
        $masterFactory = new SampleMasterFactory;
        $masterFactory->register(new CommonFactory);
        $masterFactory->register($this->createIntegrationTestFactory($masterFactory));
        $masterFactory->register(new FrontendFactory($request));
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());

        return $masterFactory;
    }

    /**
     * @param MasterFactory $masterFactory
     * @return IntegrationTestFactory
     */
    private function createIntegrationTestFactory(MasterFactory $masterFactory)
    {
        $factory = new IntegrationTestFactory($masterFactory);
        $factory->setMasterFactory($masterFactory);
        if ($this->isFirstInstantiationOfFactory()) {
            $this->retrieveInMemoryObjectsFromFactory($factory);
        } else {
            $this->injectInMemoryObjectsIntoFactory($factory);
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

    private function retrieveInMemoryObjectsFromFactory(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventQueue = $factory->getEventQueue();
        $this->commandQueue = $factory->getCommandQueue();
        $this->searchEngine = $factory->getSearchEngine();
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function injectInMemoryObjectsIntoFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventQueue($this->eventQueue);
        $factory->setCommandQueue($this->commandQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }
}
