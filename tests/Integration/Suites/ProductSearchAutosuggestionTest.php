<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionSnippetRenderer;

class ProductSearchAutosuggestionTest extends AbstractIntegrationTest
{
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

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    private function createSearchAutosuggestionSnippet()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_search_autosuggestion');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBodyString = '[]';
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

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
        $sortOrderConfigs = $this->factory->getProductSearchAutosuggestionSortOrderConfig();

        return new ProductSearchAutosuggestionRequestHandler(
            $this->factory->createContext(),
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->createSearchCriteriaBuilder(),
            $this->factory->getSearchableAttributeCodes(),
            $sortOrderConfigs
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

    public function testProductInSearchAutosuggestionSnippetsAreAddedToDataPool()
    {
        $this->importCatalog();

        $productId = ProductId::fromString('118235-251');
        $productName = 'LED Arm-Signallampe';

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $dataPoolReader = $this->factory->createDataPoolReader();
        $snippetKeyGenerator = $this->factory->createProductInSearchAutosuggestionSnippetKeyGenerator();

        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, [Product::ID => $productId]);
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
        $this->importCatalog();
        $this->createSearchAutosuggestionSnippet();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/catalogsearch/suggest/?q=adi'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $this->registerProductSearchAutosuggestionMetaSnippetKeyGenerator();
        $this->registerProductInSearchAutosuggestionSnippetKeyGenerator();

        $productSearchAutosuggestionRequestHandler = $this->getProductSearchAutosuggestionRequestHandler();
        $page = $productSearchAutosuggestionRequestHandler->process($request);
        $body = $page->getBody();

        $this->assertStringStartsWith('<ul>', $body);

        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
