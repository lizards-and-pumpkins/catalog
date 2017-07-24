<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ProductListingTest extends AbstractIntegrationTest
{
    use ProductListingTemplateIntegrationTestTrait;

    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    private function getFirstAvailableContext(): Context
    {
        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        return $context;
    }

    public function testProductListingSnippetIsWrittenIntoDataPool()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($this->factory, 'product_listings.xml');

        $urlKey = 'adidas-sale';

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $productListingSnippetKeyGenerator = $this->factory->createProductListingSnippetKeyGenerator();
        $pageInfoSnippetKey = $productListingSnippetKeyGenerator->getKeyForContext(
            $this->getFirstAvailableContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($pageInfoSnippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);

        $expectedCriteriaJson = json_encode(CompositeSearchCriterion::createAnd(
            new SearchCriterionGreaterThan('stock_qty', '0'),
            new SearchCriterionEqual('category', 'sale'),
            new SearchCriterionEqual('brand', 'Adidas')
        ));

        $this->assertEquals(ProductListingTemplateSnippetRenderer::CODE, $metaInfoSnippet['root_snippet_code']);
        $this->assertEquals($expectedCriteriaJson, json_encode($metaInfoSnippet['product_selection_criteria']));
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importProductListingTemplateFixtureViaApi();
        $this->importCatalogFixture($this->factory, 'simple_product_adilette.xml', 'product_listings.xml');

        $urlKey = 'sale';

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/' . $urlKey),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $context = $this->getFirstAvailableContext();
        $metaJson = $this->factory->createSnippetReader()->getPageMetaSnippet($urlKey, $context);
        $productListingRequestHandler = $this->factory->createProductListingRequestHandler($metaJson);
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
