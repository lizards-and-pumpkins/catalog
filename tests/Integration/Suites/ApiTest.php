<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

class ApiTest extends AbstractIntegrationTest
{
    public function testDomainEventsArePlacedIntoQueue()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $commandQueue = $factory->getCommandQueue();
        $this->assertEquals(0, $commandQueue->count());

        $website = new SampleWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertGreaterThan(0, $commandQueue->count());
    }
}
