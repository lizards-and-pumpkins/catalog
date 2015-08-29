<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductId;
use Brera\Product\ProductInSearchAutosuggestionSnippetRenderer;
use Brera\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use Brera\Product\ProductSearchAutosuggestionRequestHandler;
use Brera\Product\ProductSearchAutosuggestionSnippetRenderer;

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

        $productId = ProductId::fromString('118235-251');
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

    public function testSearchAutosuggestionHtmlIsReturned()
    {
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

        $this->importCatalog();
        $this->createSearchAutosuggestionSnippet();

        $this->registerProductSearchAutosuggestionMetaSnippetKeyGenerator();
        $this->registerProductInSearchAutosuggestionSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/catalogsearch/suggest/?q=adi'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productSearchAutosuggestionRequestHandler = $this->getProductSearchAutosuggestionRequestHandler();
        $page = $productSearchAutosuggestionRequestHandler->process($request);
        $body = $page->getBody();

        $this->assertStringStartsWith('<ul>', $body);

        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
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

    /**
     * @return ProductSearchAutosuggestionRequestHandler
     */
    private function getProductSearchAutosuggestionRequestHandler()
    {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );

        return new ProductSearchAutosuggestionRequestHandler(
            $this->factory->createContext(),
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator()
        );
    }

    private function registerProductSearchAutosuggestionMetaSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductSearchAutosuggestionMetaSnippetRenderer::CODE,
            $this->factory->createProductSearchAutosuggestionMetaSnippetKeyGenerator()
        );
    }

    private function registerProductInSearchAutosuggestionSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductInSearchAutosuggestionSnippetRenderer::CODE,
            $this->factory->createProductInSearchAutosuggestionSnippetKeyGenerator()
        );
    }
}
