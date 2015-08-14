<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductSearchRequestHandler;
use Brera\Product\ProductSearchResultMetaSnippetRenderer;

class ProductSearchTest extends AbstractIntegrationTest
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
    
    public function testProductSearchResultMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $metaInfoSnippetKeyGenerator = $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($context, []);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);

        $expectedMetaInfoContent = [
            'root_snippet_code'  => 'product_listing',
            'page_snippet_codes' => [
                'product_listing',
                'global_notices',
                'breadcrumbsContainer',
                'global_messages',
                'content_block_in_product_listing',
                'before_body_end'
            ]
        ];

        $this->assertSame(json_encode($expectedMetaInfoContent), $metaInfoSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

        $this->importCatalog();
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $this->registerProductSearchResultMetaSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/catalogsearch/result/?q=adi'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productSearchResultRequestHandler = $this->getProductSearchRequestHandler();
        $page = $productSearchResultRequestHandler->process($request);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }

    private function addTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.templates.v1+json']);
        $httpRequestBodyString = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.json');
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
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

    /**
     * @return ProductSearchRequestHandler
     */
    private function getProductSearchRequestHandler()
    {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );

        return new ProductSearchRequestHandler(
            $this->factory->getContext(),
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator()
        );
    }

    private function registerProductSearchResultMetaSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            $this->factory->createProductSearchResultMetaSnippetKeyGenerator()
        );
    }
}
