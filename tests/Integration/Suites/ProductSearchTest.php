<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;

class ProductSearchTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function addTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
        
        $this->failIfMessagesWhereLogged($this->factory->getLogger());
    }

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

    /**
     * @return ProductSearchRequestHandler
     */
    private function getProductSearchRequestHandler()
    {
        return $this->factory->createProductSearchRequestHandler();
    }

    private function registerProductSearchResultMetaSnippetKeyGenerator()
    {
        $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy()->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            function () {
                return$this->factory->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
    }

    public function testProductSearchResultMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $metaInfoSnippetKeyGenerator = $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($context, []);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);

        $expectedRootSnippetCode = 'product_listing';

        $this->assertSame($expectedRootSnippetCode, $metaInfoSnippet['root_snippet_code']);
        $this->assertContains($expectedRootSnippetCode, $metaInfoSnippet['page_snippet_codes']);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->importCatalog();
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/catalogsearch/result/?q=adi'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        
        $this->registerProductSearchResultMetaSnippetKeyGenerator();
        
        $productSearchResultRequestHandler = $this->getProductSearchRequestHandler();
        $page = $productSearchResultRequestHandler->process($request);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
