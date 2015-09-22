<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\ProductListingMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductListingRequestHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductListingTest extends AbstractIntegrationTest
{
    private $testUrl = 'http://example.com/sale';

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

    private function createProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBodyString = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.json');
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    /**
     * @param string $contentBlockContent
     */
    private function addContentBlockToDataPool($contentBlockContent)
    {
        $testUrlKey = preg_replace('/.*\//', '', $this->testUrl);
        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/in_product_listing_' . $testUrlKey);
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v1+json'
        ]);
        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'de_DE']
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function getProductListingRequestHandler()
    {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );
        $filterNavigationAttributeCodes = [];
        $defaultNumberOfProductsPerPage = 9;

        return new ProductListingRequestHandler(
            $this->factory->createContext(),
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->createFilterNavigationFilterCollection(),
            $filterNavigationAttributeCodes,
            $this->factory->createPaginationBlockRenderer(),
            $defaultNumberOfProductsPerPage
        );
    }

    /**
     * @return mixed[]
     */
    private function getStubMetaInfo()
    {
        $searchCriterion1 = SearchCriterionEqual::create('category', 'sale');
        $searchCriterion2 = SearchCriterionEqual::create('brand', 'Adidas');
        $searchCriteria = CompositeSearchCriterion::createAnd($searchCriterion1, $searchCriterion2);

        $pageSnippetCodes = [
            'global_notices',
            'breadcrumbsContainer',
            'global_messages',
            'content_block_in_product_listing',
            'before_body_end'
        ];

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $searchCriteria,
            ProductListingPageSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return $metaSnippetContent->getInfo();
    }

    private function registerProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingPageSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );
    }

    private function registerContentBlockInProductListingSnippetKeyGenerator()
    {
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            'content_block_in_product_listing',
            $this->factory->createContentBlockInProductListingSnippetKeyGenerator()
        );
    }

    public function testProductListingMetaSnippetIsWrittenIntoDataPool()
    {
        $this->importCatalog();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeyNode = (new XPathParser($xml))->getXmlNodesArrayByXPath('//catalog/listings/listing[1]/@url_key');
        $urlKey = $urlKeyNode[0]['value'];

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productListingMetaInfoSnippetKeyGenerator = $this->factory->createProductListingMetaDataSnippetKeyGenerator();
        $snippetKey = $productListingMetaInfoSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($snippetKey);

        $expectedMetaInfoContent = json_encode($this->getStubMetaInfo());

        $this->assertSame($expectedMetaInfoContent, $metaInfoSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->importCatalog();
        $this->createProductListingFixture();

        $this->registerProductListingSnippetKeyGenerator();

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Gel-Noosa';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }

    public function testContentBlockIsPresentAtProductListingPage()
    {
        $this->importCatalog();
        $this->createProductListingFixture();

        $contentBlockContent = '<div>Content Block</div>';

        $this->addContentBlockToDataPool($contentBlockContent);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($this->testUrl),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        
        $this->registerContentBlockInProductListingSnippetKeyGenerator();

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        $this->assertContains($contentBlockContent, $body);
    }
}
