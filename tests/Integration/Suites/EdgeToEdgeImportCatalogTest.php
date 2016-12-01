<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\Routing\HttpResourceNotFoundResponse;
use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductListing\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\XPathParser;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

class EdgeToEdgeImportCatalogTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function importCatalog(string $importFileName)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    public function testCatalogImportDomainEventPutsProductToKeyValueStoreAndSearchIndex()
    {
        $productId = new ProductId('118235-251');
        $productName = 'LED Arm-Signallampe';
        $expectedProductPrice = Price::fromDecimalValue(11.45)->getAmount();

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
            $contextDataSet = [Country::CONTEXT_CODE => $country];
            $contextWithCountry = $this->factory->createContextBuilder()->expandContext($context, $contextDataSet);

            $priceSnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('price');
            $priceSnippetKey = $priceSnippetKeyGenerator->getKeyForContext(
                $contextWithCountry,
                [Product::ID => $productId]
            );
            $priceSnippetContents = $dataPoolReader->getSnippet($priceSnippetKey);
            $this->assertEquals($expectedProductPrice, $priceSnippetContents);
        }

        $criteria = new SearchCriterionEqual('name', 'LED Arm-Signallampe');
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult;
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortBy = new SortBy(AttributeCode::fromString('name'), SortDirection::create(SortDirection::ASC));
        $queryOptions = QueryOptions::create(
            $selectedFilters,
            $context,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortBy
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
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);
        
        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertContains('<body>', $response->getBody());
    }

    public function testImportedProductIsAccessibleViaNonCanonicalUrlFromTheFrontend()
    {
        $this->importCatalog('catalog.xml');

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="non_canonical_url_key" and @locale="fr_FR"]'
        );

        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKeys[0]['value']);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertContains('<body>', $response->getBody());
    }

    public function testHttpResourceNotFoundResponseIsReturned()
    {
        $url = HttpUrl::fromString('http://example.com/non/existent/path');
        $headers = HttpHeaders::fromArray([]);
        $requestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $url, $headers, $requestBody);

        $masterFactory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($masterFactory);

        $website = new DefaultWebFront($request, $implementationSpecificFactory);

        $response = $website->processRequest();
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

        $validProductId = new ProductId('288193NEU');
        $validProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $validProductId]
        );

        $invalidProductId = new ProductId('T4H2N-4701');
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
