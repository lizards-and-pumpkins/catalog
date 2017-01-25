<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class CurrentVersionApiTest extends AbstractIntegrationTest
{
    public function testReturnsDefaultCurrentVersionAndEmptyPreviousVersion()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray(['Accept' => 'application/vnd.lizards-and-pumpkins.current_version.v1+json']),
            new HttpRequestBody('')
        );
        
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $website = new InjectableDefaultWebFront($request, $factory, $this->getIntegrationTestFactory($factory));
        $response = $website->processRequest();
        $body = json_decode($response->getBody(), true);
        
        $this->assertInternalType('array', $body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('current_version', $body['data']);
        $this->assertNotEmpty($body['data']['current_version']);
        $this->assertSame('', $body['data']['previous_version']);
    }
}
