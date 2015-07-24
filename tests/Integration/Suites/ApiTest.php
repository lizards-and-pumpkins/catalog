<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportDomainEvent;

class ApiTest extends AbstractIntegrationTest
{
    public function testCatalogImportDomainEventWithCorrectPayloadIsPlacedIntoQueue()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainEventQueue = $factory->getEventQueue();
        $this->assertEquals(0, $domainEventQueue->count());

        $website = new SampleWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertEquals(1, $domainEventQueue->count());

        /** @var CatalogImportDomainEvent $domainEvent */
        $domainEvent = $domainEventQueue->next();
        $expectedContents = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');

        $this->assertInstanceOf(CatalogImportDomainEvent::class, $domainEvent);
        $this->assertEquals($expectedContents, $domainEvent->getXml());
    }
}
