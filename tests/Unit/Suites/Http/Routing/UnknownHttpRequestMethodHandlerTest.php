<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodHandler
 * @uses   \LizardsAndPumpkins\Http\HttpUnknownMethodRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 */
class UnknownHttpRequestMethodHandlerTest extends TestCase
{
    private function createHttpRequestWithMethod(string $methodCode): HttpRequest
    {
        return HttpRequest::fromParameters(
            $methodCode,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
    }
    
    public function testIsARequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, new UnknownHttpRequestMethodHandler());
    }

    public function testReturnsMethodNotAllowedHttpResponse()
    {
        $unknownMethodRequest = $this->createHttpRequestWithMethod('FOO');
        $response = (new UnknownHttpRequestMethodHandler())->process($unknownMethodRequest);
        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', $response->getBody());
    }
}
