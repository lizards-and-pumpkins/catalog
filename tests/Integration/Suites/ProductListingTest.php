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
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ProductListingTest extends AbstractIntegrationTest
{
    /**
     * @var MasterFactory
     */
    private $factory;

    protected function importProductListingTemplateFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }
    
    public function testProductListingSnippetIsWrittenIntoDataPool()
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
        $metaInfoSnippetJson = $dataPoolReader->getSnippet($pageInfoSnippetKey);
        $metaInfoSnippet = json_decode($metaInfoSnippetJson, true);
        
        $titleKeyGenerator = $this->factory->createProductListingTitleSnippetKeyGenerator();
        $titleKey = $titleKeyGenerator->getKeyForContext($context, [PageMetaInfoSnippetContent::URL_KEY => $urlKey]);
        $titleSnippet = $dataPoolReader->getSnippet($titleKey);
        $this->assertSame('Adidas Rausverkauf!', $titleSnippet);
        
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
        $this->importProductListingTemplateFixture();
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

        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $expectedMetaDescription = 'Acheter des chaussures de sport moins chères ? C’est possible grâce à
                    nos offres à prix discount. Commandez très simplement vos futures chaussures de course qui vous
                    seront expédiées rapidement.';
        
        $expectedKeywords = 'vendre, offre, proposition';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);

        $this->assertContains($expectedMetaDescription, $body);
        $this->assertContains($expectedKeywords, $body);
    }
}
