<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResourceNotFoundResponse;
use LizardsAndPumpkins\Log\LogMessage;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Utils\XPathParser;

class EdgeToEdgeImportCatalogTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    /**
     * @param string $importFileName
     */
    private function importCatalog($importFileName)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableDefaultWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    public function testCatalogImportDomainEventPutsProductToKeyValueStoreAndSearchIndex()
    {
        $productId = ProductId::fromString('118235-251');
        $productName = 'LED Arm-Signallampe';
        $productPrice = 1145;

        $this->importCatalog('catalog.xml');

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $keyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productDetailViewKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductDetailViewSnippetRenderer::CODE
        );
        $productDetailViewKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $productId]
        );
        $productDetailViewHtml = $dataPoolReader->getSnippet($productDetailViewKey);

        $this->assertContains(
            (string) $productId,
            $productDetailViewHtml,
            sprintf('The result page HTML does not contain the expected sku "%s"', $productId)
        );
        $this->assertContains(
            $productName,
            $productDetailViewHtml,
            sprintf('The result page HTML does not contain the expected product name "%s"', $productName)
        );

        $listingPageKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        $listingPageKey = $listingPageKeyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        $productListingHtml = $dataPoolReader->getSnippet($listingPageKey);

        $this->assertContains(
            $productName,
            $productListingHtml,
            sprintf('Product in listing snippet HTML does not contain the expected product name "%s"', $productName)
        );

        foreach ($this->factory->createTaxableCountries() as $country) {
            $contextDataSet = [ContextCountry::CODE => $country];
            $contextWithCountry = $this->factory->createContextBuilder()->expandContext($context, $contextDataSet);

            $priceSnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('price');
            $priceSnippetKey = $priceSnippetKeyGenerator->getKeyForContext(
                $contextWithCountry,
                [Product::ID => $productId]
            );
            $priceSnippetContents = $dataPoolReader->getSnippet($priceSnippetKey);
            $this->assertEquals($productPrice, $priceSnippetContents);
        }

        $criteria = SearchCriterionEqual::create('name', 'LED Arm-Signallampe');
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult;
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = SortOrderConfig::create(
            AttributeCode::fromString('name'),
            SortOrderDirection::create(SortOrderDirection::ASC)
        );
        $queryOptions = new QueryOptions(
            $selectedFilters,
            $context,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $searchResults = $dataPoolReader->getSearchResultsMatchingCriteria($criteria, $queryOptions);

        $this->assertContains($productId, $searchResults->getProductIds(), '', false, false);
    }

    public function testImportedProductIsAccessibleFromTheFrontend()
    {
        $this->importCatalog('catalog.xml');

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="url_key" and @locale="fr_FR"]'
        );

        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKeys[0]['value']);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $this->prepareIntegrationTestMasterFactoryForRequest($request);
        
        $website = new InjectableDefaultWebFront($request, $this->factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertContains('<body>', $response->getBody());
    }

    public function testHttpResourceNotFoundResponseIsReturned()
    {
        $url = HttpUrl::fromString('http://example.com/non/existent/path');
        $headers = HttpHeaders::fromArray([]);
        $requestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $url, $headers, $requestBody);

        $website = new DefaultWebFront($request);
        new IntegrationTestFactory($website->getMasterFactory());
        $response = $website->runWithoutSendingResponse();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $response);
    }
    
    public function testProductsWithValidDataAreImportedAndInvalidDataAreNotImportedButLogged()
    {
        $this->importCatalog('catalog-with-invalid-product.xml');

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $keyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $productDetailViewKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductDetailViewSnippetRenderer::CODE
        );

        $validProductId = ProductId::fromString('288193NEU');
        $validProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $validProductId]
        );

        $invalidProductId = ProductId::fromString('T4H2N-4701');
        $invalidProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $invalidProductId]
        );

        $dataPoolReader = $this->factory->createDataPoolReader();
        $this->assertTrue(
            $dataPoolReader->hasSnippet($validProductDetailViewSnippetKey),
            sprintf('Expected snippet "%s" not found in data pool', $validProductDetailViewSnippetKey)
        );
        $this->assertFalse(
            $dataPoolReader->hasSnippet($invalidProductDetailViewSnippetKey),
            sprintf('Unexpected product snippet "%s" found in data pool', $invalidProductDetailViewSnippetKey)
        );

        $logger = $this->factory->getLogger();
        $messages = $logger->getMessages();
        
        $importExceptionMessage = 'The attribute "price" has multiple values with ' .
            'different contexts which can not be part of one product attribute list';
        $expectedLoggedErrorMessage = sprintf(
            'Error during processing catalog product XML import for product "%s": %s',
            $invalidProductId,
            $importExceptionMessage
        );
        $this->assertContains($expectedLoggedErrorMessage, $messages, 'Product import failure was not logged.');
        
        if (count($messages) > 0) {
            array_map(function (LogMessage $message) use ($expectedLoggedErrorMessage) {
                if ($expectedLoggedErrorMessage != $message) {
                    $this->fail($message->getContextSynopsis());
                }
            }, $messages);
        }
    }
}
