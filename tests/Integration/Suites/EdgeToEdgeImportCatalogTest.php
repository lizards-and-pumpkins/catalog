<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpResponse;
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
use SebastianBergmann\Money\Currency;

class EdgeToEdgeImportCatalogTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function importCatalogFixtureWithApiV1(string $importFileName)
    {
        $httpRequestBody = $this->buildV1ApiCatalogImportRequestBody($importFileName);
        $this->importCatalogFixtureWithApi($httpRequestBody, 'v1');
    }

    private function importCatalogFixtureWithApiV2(string $importFileName, string $dataVersion)
    {
        $httpRequestBody = $this->buildV2ApiCatalogImportRequestBody($importFileName, $dataVersion);
        $this->importCatalogFixtureWithApi($httpRequestBody, 'v2');
    }

    private function buildV1ApiCatalogImportRequestBody(string $importFileName): HttpRequestBody
    {
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        return new HttpRequestBody($httpRequestBodyString);
    }

    private function buildV2ApiCatalogImportRequestBody(string $importFileName, string $dataVersion): HttpRequestBody
    {
        $httpRequestBodyString = json_encode(['fileName' => $importFileName, 'dataVersion' => $dataVersion]);
        return new HttpRequestBody($httpRequestBodyString);
    }

    private function importCatalogFixtureWithApi(HttpRequestBody $httpRequestBody, $apiVersion)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.' . $apiVersion . '+json'
        ]);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }

    private function getProductUrlKeyFromFixture(string $fixtureFile): string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        return (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="non_canonical_url_key" and @locale="fr_FR"]'
        )[0]['value'];
    }

    private function getProductNameFromFixture(string $fixtureFile): string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        return (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="name" and @locale="fr_FR"]'
        )[0]['value'];
    }

    private function getProductPriceFromFixture(string $fixtureFile): string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        return (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="price" and @website="fr"]'
        )[0]['value'];
    }

    private function getHttpRequestResponse(string $urlKey): HttpResponse
    {
        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKey);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);

        return $website->processRequest();
    }
    
    public function assertProductCanBeAccessedOnFrontend(string $urlKey, string $name, string $dataVersion)
    {
        $this->factory->createDataPoolWriter()->setCurrentDataVersion($dataVersion);
        $response = $this->getHttpRequestResponse($urlKey);
        if (! $response->getStatusCode() === HttpResponse::STATUS_OK) {
            $this->fail(sprintf('Product "%s" not accessible on route "%s"', $name, $urlKey));
        }
        $this->assertContains($name, $response->getBody());
    }
    
    public function assertProductPriceOnFrontend(string $urlKey, string $dataVersion, string $expectedPrice)
    {
        $price = Price::fromDecimalValue($expectedPrice)->round((new Currency('EUR'))->getDefaultFractionDigits());
        $this->factory->createDataPoolWriter()->setCurrentDataVersion($dataVersion);
        $response = $this->getHttpRequestResponse($urlKey);
        if (! $response->getStatusCode() === HttpResponse::STATUS_OK) {
            $this->fail(sprintf('Product not accessible on route "%s"', $urlKey));
        }
        $this->assertContains((string) $price->getAmount(), $response->getBody());
    }

    public function testCatalogImportApiPutsProductIntoKeyValueStoreAndSearchIndex()
    {
        $productId = new ProductId('118235-251');
        $productName = 'LED Arm-Signallampe (1)';
        $expectedProductPrice = Price::fromDecimalValue(11.45)->getAmount();

        $this->importCatalogFixtureWithApiV1('simple_product_armflasher-v1.xml');

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

        $criteria = new SearchCriterionEqual('name', $productName);
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
        $searchResults = $dataPoolReader->getSearchResults($criteria, $queryOptions);

        $productIds = $searchResults->getProductIds();
        $this->assertContains($productId, $productIds, '', false, false);
    }

    public function testImportedProductIsAccessibleFromTheFrontend()
    {
        $fixtureFile = 'simple_product_armflasher-v1.xml';
        $this->importCatalogFixtureWithApiV1($fixtureFile);
        
        $this->assertProductCanBeAccessedOnFrontend(
            $this->getProductUrlKeyFromFixture($fixtureFile),
            $this->getProductNameFromFixture($fixtureFile),
            $dataVersion = '-1'
        );
    }

    public function testImportedProductIsAccessibleViaNonCanonicalUrlFromTheFrontend()
    {
        $fixtureFile = 'simple_product_armflasher-v1.xml';
        $this->importCatalogFixtureWithApiV1($fixtureFile);

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        $urlKey = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/attribute[@name="non_canonical_url_key" and @locale="fr_FR"]'
        )[0]['value'];

        $name = $this->getProductNameFromFixture($fixtureFile);
        $this->assertProductCanBeAccessedOnFrontend($urlKey, $name, $dataVersion = '-1');
    }

    public function testHttpResourceNotFoundResponseIsReturned()
    {
        $url = HttpUrl::fromString('http://example.com/non/existent/path');
        $headers = HttpHeaders::fromArray([]);
        $requestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $url, $headers, $requestBody);

        $masterFactory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($masterFactory);

        $website = new InjectableDefaultWebFront($request, $masterFactory, $implementationSpecificFactory);

        $response = $website->processRequest();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $response);
    }

    public function testProductsWithValidDataAreImportedAndInvalidDataAreNotImportedButLogged()
    {
        $this->importCatalogFixtureWithApiV1('catalog-with-invalid-product.xml');

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

    public function testProductImportedWithDifferentDataVersionsAreBothAccessibleFromFrontend()
    {
        $fixtureFileV1 = 'simple_product_armflasher-v1.xml';
        $fixtureFileV2 = 'simple_product_armflasher-v2.xml';
        $dataVersion1 = 'data-version-1';
        $dataVersion2 = 'data-version-2';
        
        $this->importCatalogFixtureWithApiV2($fixtureFileV1, $dataVersion1);
        $this->importCatalogFixtureWithApiV2($fixtureFileV2, $dataVersion2);

        $name1 = $this->getProductNameFromFixture($fixtureFileV1);
        $name2 = $this->getProductNameFromFixture($fixtureFileV2);
        $urlKey1 = $this->getProductUrlKeyFromFixture($fixtureFileV1);
        $urlKey2 = $this->getProductUrlKeyFromFixture($fixtureFileV2);

        $this->factory->createDataPoolWriter()->setCurrentDataVersion('-1');
        $response = $this->getHttpRequestResponse($urlKey1);
        $this->assertSame(HttpResponse::STATUS_NOT_FOUND, $response->getStatusCode());
        
        $this->assertProductCanBeAccessedOnFrontend($urlKey1, $name1, $dataVersion1);
        $this->assertProductCanBeAccessedOnFrontend($urlKey2, $name2, $dataVersion2);
    }

    public function testProductPriceIsProjectedWithoutDataVersion()
    {
        $fixtureFileV1 = 'simple_product_armflasher-v1.xml';
        $fixtureFileV2 = 'simple_product_armflasher-v2.xml';
        $dataVersion1 = 'data-version-1';
        $dataVersion2 = 'data-version-2';

        $this->importCatalogFixtureWithApiV2($fixtureFileV1, $dataVersion1);
        $this->importCatalogFixtureWithApiV2($fixtureFileV2, $dataVersion2);

        $urlKey1 = $this->getProductUrlKeyFromFixture($fixtureFileV1);
        $urlKey2 = $this->getProductUrlKeyFromFixture($fixtureFileV2);
        $expectedPrice = $this->getProductPriceFromFixture($fixtureFileV2);
        
        $this->assertProductPriceOnFrontend($urlKey1, $dataVersion1, $expectedPrice);
        $this->assertProductPriceOnFrontend($urlKey2, $dataVersion2, $expectedPrice);
    }
}
