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
    /**
     * @param string $expectedProductId
     * @param array[] $productsData
     */
    private function assertContainsProductData(string $expectedProductId, array $productsData)
    {
        $found = array_reduce($productsData, function ($found, array $productData) use ($expectedProductId) {
            return $found || $productData['product_id'] === (string) $expectedProductId;
        });
        $message = sprintf('Failed to find expected product id "%s" in product data array', $expectedProductId);
        $this->assertTrue($found, $message);
    }

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

        $this->assertEquals(json_encode(['data' => []]), $response->getBody());
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
        $matches = json_decode($response->getBody(), true)['data'];

        $this->assertCount(count($expectedProductIds), $matches);

        $this->assertEquals($matches[0]['product_id'], $expectedProductIds['Adipure']);
        $this->assertEquals($matches[1]['product_id'], $expectedProductIds['Adilette']);
    }
}
