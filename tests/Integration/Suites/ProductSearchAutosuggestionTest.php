<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductId;
use Brera\Product\ProductSearchAutosuggestionSnippetRenderer;
use Brera\Product\SampleSku;

class ProductSearchAutosuggestionTest extends AbstractIntegrationTest
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactory($this->request);
    }

    public function testProductInSearchAutosuggestionSnippetsAreAddedToDataPool()
    {
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

        $this->importCatalog();

        $sku = SampleSku::fromString('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Arm-Signallampe';

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $dataPoolReader = $this->factory->createDataPoolReader();
        $snippetKeyGenerator = $this->factory->createProductInSearchAutosuggestionSnippetKeyGenerator();

        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productId]);
        $snippet = $dataPoolReader->getSnippet($snippetKey);

        $this->assertContains($productName, $snippet);
    }

    public function testSearchAutosuggestionSnippetIsAddedToDataPool()
    {
        $this->createSearchAutosuggestionSnippet();

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $keyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $keyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchAutosuggestionSnippetRenderer::CODE
        );

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $key = $keyGenerator->getKeyForContext($context, []);
        $html = $dataPoolReader->getSnippet($key);

        $expectation = '<li class="no-thumbnail">';

        $this->assertContains($expectation, $html);
    }

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function createSearchAutosuggestionSnippet()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_search_autosuggestion');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.templates.v1+json']);
        $httpRequestBodyString = '[]';
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }
}
