<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class CatalogImportApiSingleProductTest extends AbstractIntegrationTest
{
    public function testV1ProductImportApiV1PutRequestHandler()
    {
        $testDataVersionString = 'foo-123';
        $httpUrl = HttpUrl::fromString('http://example.com/api/product_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.product_import.v1+json',
        ]);

        $sku = 'led-arm-signallampe';
        $productJson = json_encode([
            'sku'        => $sku,
            'type'       => 'simple',
            'tax_class'  => 'class-1',
            'attributes' => [
                'backorders'  => true,
                'url_key'     => 'led-arm-signallampe',
                'description' => 'LED Arm-Signallampe<br />
<br />
LED Arm-Signallampe mit elastischem Band und Flasher mit variabler Blinkfolge,
Flasher abnehmbar.',
            ],
        ]);

        $httpRequestBodyString = json_encode(['product_data' => $productJson, 'data_version' => $testDataVersionString]);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($factory);

        $commandQueue = $factory->getCommandMessageQueue();
        $this->assertEquals(0, $commandQueue->count());

        $website = new InjectableRestApiWebFront($request, $factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());

        $this->processAllMessages($factory);

        $simpleProductSnippet = TestDataPoolQuery::getProductJsonSnippetForId($factory, $sku, $testDataVersionString);

        $this->assertNotEmpty($simpleProductSnippet);
        $this->failIfMessagesWhereLogged($factory->getLogger());
    }
}
