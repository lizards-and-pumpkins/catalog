<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ProductSearchEdgeToEdgeTest extends AbstractIntegrationTest
{
    use ProductListingTemplateIntegrationTestTrait;

    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    private function addTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableRestApiWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);

        $this->failIfMessagesWhereLogged($this->factory->getLogger());
    }

    private function getFirstAvailableContext(): Context
    {
        return $this->factory->createContextSource()->getAllAvailableContexts()[1];
    }

    private function getProductSearchRequestHandler(): ProductSearchRequestHandler
    {
        $urlKey = 'catalogsearch/result';
        $context = $this->getFirstAvailableContext();

        $metaJson = $this->factory->createSnippetReader()->getPageMetaSnippet($urlKey, $context);

        return $this->factory->createProductSearchRequestHandler($metaJson);
    }

    private function registerProductSearchResultMetaSnippetKeyGenerator()
    {
        $this->factory->createRegistrySnippetKeyGeneratorLocatorStrategy()->register(
            ProductSearchResultMetaSnippetRenderer::CODE,
            function () {
                return $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
            }
        );
    }

    final protected function setUp()
    {
        $this->importProductListingTemplateFixtureViaApi();
    }

    public function testProductSearchResultMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $context = $this->getFirstAvailableContext();

        $keyGeneratorParams = [PageMetaInfoSnippetContent::URL_KEY => 'catalogsearch/result'];
        $metaInfoSnippetKeyGenerator = $this->factory->createProductSearchResultMetaSnippetKeyGenerator();
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($context, $keyGeneratorParams);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);

        $expectedRootSnippetCode = 'product_listing';

        $this->assertSame($expectedRootSnippetCode, $metaInfoSnippet['root_snippet_code']);
        $this->assertContains($expectedRootSnippetCode, $metaInfoSnippet['page_snippet_codes']);
    }

    public function testProductListingPageHtmlIsReturned(): HttpResponse
    {
        $this->addTemplateWasUpdatedDomainEventToSetupProductListingFixture();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/catalogsearch/result/?q=adi'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $this->importCatalogFixture($this->factory, 'simple_product_armflasher-v1.xml', 'simple_product_adilette.xml');

        $this->registerProductSearchResultMetaSnippetKeyGenerator();

        $productSearchResultRequestHandler = $this->getProductSearchRequestHandler();
        $page = $productSearchResultRequestHandler->process($request);
        $body = $page->getBody();

        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';
        $outOfStockProductName = 'Adilette Out Of Stock';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
        $this->assertNotContains($outOfStockProductName, $body);

        return $page;
    }
}
