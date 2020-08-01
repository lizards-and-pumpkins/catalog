<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class UnknownHttpRequestTest extends AbstractIntegrationTest
{
    private function processRequest($request): Http\HttpResponse
    {
        $masterFactory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $webFront = new CatalogWebFront($request, $this->getIntegrationTestFactory($masterFactory));

        return $webFront->processRequest();
    }

    public function testWebFrontReturnsA405MethodNotAllowedResponseForUnknownRequestMethods()
    {
        $request = HttpRequest::fromParameters(
            'FOO',
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $response = $this->processRequest($request);
        
        $this->assertSame(405, $response->getStatusCode());
    }
}
