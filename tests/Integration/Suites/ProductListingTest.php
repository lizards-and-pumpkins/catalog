<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductListingTest extends AbstractIntegrationTest
{
    use ProductListingTemplateIntegrationTestTrait;

    /**
     * @var MasterFactory
     */
    private $factory;

    public function testProductListingMetaSnippetIsWrittenIntoDataPool()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($this->factory, 'product_listings.xml');

        $urlKey = 'adidas-sale';

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productListingSnippetKeyGenerator = $this->factory->createProductListingSnippetKeyGenerator();
        $pageInfoSnippetKey = $productListingSnippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaSnippetJson = $dataPoolReader->getSnippet($pageInfoSnippetKey);
        $metaSnippet = json_decode($metaSnippetJson, true);
        
        $expectedCriteriaJson = json_encode(CompositeSearchCriterion::createAnd(
            new SearchCriterionGreaterThan('stock_qty', '0'),
            new SearchCriterionEqual('category', 'sale'),
            new SearchCriterionEqual('brand', 'Adidas')
        ));

        $expectedMetaSnippetContent = [
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ProductListingTemplateSnippetRenderer::CODE,
            ProductListingMetaSnippetContent::KEY_CRITERIA => json_decode($expectedCriteriaJson, true),
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [
                'product_listing',
                'title',
                'product_listing_content_block_top'
            ],
            PageMetaInfoSnippetContent::KEY_PAGE_SPECIFIC_DATA => [
                'meta_title' => 'Adidas Rausverkauf!',
                'meta_description' => 'Adidas Rausverkauf! Greifen Sie jetzt zu!',
            ],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => []
        ];

        $this->assertEquals($expectedMetaSnippetContent, $metaSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importProductListingTemplateFixtureViaApi();
        $this->importCatalogFixture($this->factory, 'simple_product_adilette.xml', 'product_listings.xml');

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/sale'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $productListingRequestHandler = $this->factory->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);
        $body = $page->getBody();

        $expectedPageTitle = 'Vendre';
        $expectedMetaDescription = json_encode('Acheter des chaussures de sport moins chères ? C’est possible grâce à
                    nos offres à prix discount. Commandez très simplement vos futures chaussures de course qui vous
                    seront expédiées rapidement.');
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedPageTitle, $body);
        $this->assertContains($expectedMetaDescription, $body);
        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
}
