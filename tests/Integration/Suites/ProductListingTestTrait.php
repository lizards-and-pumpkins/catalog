<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

trait ProductListingTestTrait
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
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->createIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function prepareProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->createIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->createIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

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

    private function createProductListingRequestHandler() : ProductListingRequestHandler
    {
        return $this->factory->createProductListingRequestHandler();
    }

    private function createIntegrationTestMasterFactoryForRequest(HttpRequest $request) : SampleMasterFactory
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register($this->createIntegrationTestFactory($masterFactory));
        $masterFactory->register(new ProductSearchFactory());
        $masterFactory->register(new FrontendFactory($request));
        $masterFactory->register(new UpdatingProductImportCommandFactory());
        $masterFactory->register(new UpdatingProductImageImportCommandFactory());
        $masterFactory->register(new UpdatingProductListingImportCommandFactory());

        return $masterFactory;
    }

    private function createIntegrationTestFactory(MasterFactory $masterFactory) : IntegrationTestFactory
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

    private function isFirstInstantiationOfFactory() : bool
    {
        return null === $this->keyValueStore;
    }

    private function retrieveInMemoryObjectsFromFactory(IntegrationTestFactory $factory)
    {
        $this->keyValueStore = $factory->getKeyValueStore();
        $this->eventMessageQueue = $factory->getEventMessageQueue();
        $this->commandMessageQueue = $factory->getCommandMessageQueue();
        $this->searchEngine = $factory->getSearchEngine();
        $this->urlKeyStore = $factory->getUrlKeyStore();
    }

    private function injectInMemoryObjectsIntoFactory(IntegrationTestFactory $factory)
    {
        $factory->setKeyValueStore($this->keyValueStore);
        $factory->setEventMessageQueue($this->eventMessageQueue);
        $factory->setCommandMessageQueue($this->commandMessageQueue);
        $factory->setSearchEngine($this->searchEngine);
        $factory->setUrlKeyStore($this->urlKeyStore);
    }
}
