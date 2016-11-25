<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory;

class ProductSearchApiTest extends AbstractIntegrationTest
{
    public function testEmptyJsonIsReturnedIfNoProductsMatchTheRequest()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?q=morrissey');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $factory->register(new ProductSearchFactory());

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory);

        $website = new InjectableDefaultWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertEquals(json_encode(['total' => 0, 'data' => []]), $response->getBody());
    }

    public function testProductDetailsMatchingRequestSortedDescendingByStockQuantityAreReturned()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/product/?q=adi');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.product.v1+json']);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $factory->register(new ProductSearchFactory());

        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);
        $this->importCatalogFixture($factory);

        $website = new InjectableDefaultWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $expectedProductIds = ['Adilette' => '288193NEU', 'Adipure' => 'M29540'];
        $responseJson = json_decode($response->getBody(), true);

        $this->assertCount(count($expectedProductIds), $responseJson['data']);
        $this->assertSame(count($expectedProductIds), $responseJson['total']);

        $this->assertEquals($responseJson['data'][0]['product_id'], $expectedProductIds['Adipure']);
        $this->assertEquals($responseJson['data'][1]['product_id'], $expectedProductIds['Adilette']);
    }
}
