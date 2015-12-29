<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class ProductRelationsApiTest extends AbstractIntegrationTest
{
    public function testItReturnsAnEmptyArrayForRelatedModelsWWithAnEmptyDataPool()
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

        $this->assertEquals(json_encode([]), $response->getBody());
    }
}
