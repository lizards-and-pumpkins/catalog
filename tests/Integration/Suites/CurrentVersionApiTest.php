<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;

class CurrentVersionApiTest extends AbstractIntegrationTest
{
    private function createReadCurrentVersionRequest(): HttpRequest
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.current_version.v1+json']),
            new HttpRequestBody('')
        );
    }

    private function createUpdateCurrentVersionRequest(string $targetVersion): HttpRequest
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_PUT,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.current_version.v1+json']),
            new HttpRequestBody(json_encode(['current_version' => $targetVersion]))
        );
    }

    private function processRequest($request): HttpResponse
    {
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $website = new InjectableRestApiWebFront($request, $factory, $this->getIntegrationTestFactory($factory));

        return $website->processRequest();
    }

    private function getResponseBody($request): string
    {
        return $this->processRequest($request)->getBody();
    }

    public function testReturnsDefaultCurrentVersionAndEmptyPreviousVersion()
    {
        $request = $this->createReadCurrentVersionRequest();

        $responseData = json_decode($this->getResponseBody($request), true);
        
        $this->assertInternalType('array', $responseData);
        $this->assertNotEmpty($responseData['data']['current_version']);
        $this->assertSame('', $responseData['data']['previous_version']);
    }

    public function testSetAndReadCurrentDataVersion()
    {
        $readRequest = $this->createReadCurrentVersionRequest();
        $firstResponseData = json_decode($this->getResponseBody($readRequest), true);
        $originalVersion = $firstResponseData['data']['current_version'];
        
        $targetVersion = uniqid('test-');
        $response = $this->processRequest($this->createUpdateCurrentVersionRequest($targetVersion));
        $this->assertSame(HttpResponse::STATUS_ACCEPTED, $response->getStatusCode());

        $factory = $this->prepareIntegrationTestMasterFactory();
        $this->processAllMessages($factory);
        $this->failIfMessagesWhereLogged($factory->getLogger());

        $secondResponseData = json_decode($this->getResponseBody($readRequest), true);
        $this->assertSame($targetVersion, $secondResponseData['data']['current_version']);
        $this->assertSame($originalVersion, $secondResponseData['data']['previous_version']);
    }
}
