<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class RelatedModelsProductRelationsApiTest extends AbstractIntegrationTest
{
    /**
     * @param string $expectedProductId
     * @param array[] $productsData
     */
    private function assertContainsProductData($expectedProductId, array $productsData)
    {
        $found = array_reduce($productsData, function ($found, array $productData) use ($expectedProductId) {
            return $found || $productData['product_id'] === (string) $expectedProductId;
        });
        $message = sprintf('Failed to find expected product id "%s" in product data array', $expectedProductId);
        $this->assertTrue($found, $message);
    }
    
    public function testNoRelatedModelsAreReturnsForAProductWithoutSharedBrandAndGender()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/products/118235-251/relations/related-models');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.product_relations.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $this->importCatalogFixture($factory);

        $website = new InjectableDefaultWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals(json_encode(['data' => []]), $response->getBody());
    }

    public function testRelatedProductsWithMatchingBrandAndGenderAreReturned()
    {
        $testProductId = 'T500N-4207';
        $expectedProductIds = ['T4H2N-4701', 'T408Q-9030'];
        
        $httpUrl = HttpUrl::fromString('http://example.com/api/products/' . $testProductId . '/relations/related-models');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.product_relations.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $this->importCatalogFixture($factory);

        $website = new InjectableDefaultWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();
        $matches = json_decode($response->getBody(), true)['data'];

        $this->assertCount(count($expectedProductIds), $matches);
        
        foreach ($expectedProductIds as $expectedProductId) {
            $this->assertContainsProductData($expectedProductId, $matches);
        }
    }
}
