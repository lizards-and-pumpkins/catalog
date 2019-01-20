<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class ProductSearchApiTest extends AbstractIntegrationTest
{
    public function testReturnsEmptyJsonIfNoProductsMatchTheRequest()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?q=morrissey');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertEquals(json_encode(['total' => 0, 'data' => [], 'facets' => []]), $response->getBody());
    }

    public function testReturnsProductsMatchingRequestSortedDescendingByStockQuantity()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?q=adi');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory, 'simple_product_adilette.xml', 'configurable_product_adipure.xml');

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $expectedProductIds = ['Adilette' => '288193NEU', 'Adipure' => 'M29540'];
        $responseJson = json_decode($response->getBody(), true);

        $this->assertCount(count($expectedProductIds), $responseJson['data']);
        $this->assertSame(count($expectedProductIds), $responseJson['total']);

        $this->assertEquals($responseJson['data'][0]['product_id'], $expectedProductIds['Adipure']);
        $this->assertEquals($responseJson['data'][1]['product_id'], $expectedProductIds['Adilette']);
    }

    public function testReturnsProductWithSelectedFiltersApplied()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?filters=brand:Adidas');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory, 'simple_product_adilette.xml', 'configurable_product_adipure.xml');

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $expectedProductIds = ['Adilette' => '288193NEU', 'Adipure' => 'M29540'];
        $responseJson = json_decode($response->getBody(), true);

        $this->assertCount(count($expectedProductIds), $responseJson['data']);
        $this->assertSame(count($expectedProductIds), $responseJson['total']);

        $this->assertEquals($responseJson['data'][0]['product_id'], $expectedProductIds['Adipure']);
        $this->assertEquals($responseJson['data'][1]['product_id'], $expectedProductIds['Adilette']);
    }

    public function testReturnsFacets()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?facets=brand,gender');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory, 'simple_product_adilette.xml', 'configurable_product_adipure.xml');

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $expectedFacets = [
            'brand' => [
                ['value' => 'Adidas', 'count' => 2],
            ],
            'gender' => [
                ['value' => 'Damen', 'count' => 1],
                ['value' => 'Herren', 'count' => 1],
            ],
        ];

        $responseJson = json_decode($response->getBody(), true);

        $this->assertEquals($expectedFacets, $responseJson['facets']);
    }

    public function testAllowsWhitespacesInSearchTerm()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?q=Armer%20Conduit');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory, 'simple_product_armflasher-v1.xml');

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $responseJson = json_decode($response->getBody(), true);

        $this->assertCount(1, $responseJson['data']);
        $this->assertSame(1, $responseJson['total']);

        $this->assertEquals('118235-251', $responseJson['data'][0]['product_id']);
    }

    public function testAllowsWhitespacesInCriteria()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?criteria=brand:Pro%20Touch');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory, 'simple_product_armflasher-v1.xml');

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $responseJson = json_decode($response->getBody(), true);

        $this->assertCount(1, $responseJson['data']);
        $this->assertSame(1, $responseJson['total']);

        $this->assertEquals('118235-251', $responseJson['data'][0]['product_id']);
    }
}
