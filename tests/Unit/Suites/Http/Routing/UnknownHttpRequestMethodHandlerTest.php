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
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
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

    public function testCanHandleUnknownHttpMethodRequests()
    {
        $unknownMethodHttpRequest = $this->createHttpRequestWithMethod('FOO');
        $this->assertTrue((new UnknownHttpRequestMethodHandler())->canProcess($unknownMethodHttpRequest));
    }

    /**
     * @param HttpRequest $httpRequestWithKnownMethod
     * @dataProvider knownHttpRequestMethodCodesProvider 
     */
    public function testCanNotHandleKnownHttpMethodRequests(HttpRequest $httpRequestWithKnownMethod)
    {
        $this->assertFalse((new UnknownHttpRequestMethodHandler())->canProcess($httpRequestWithKnownMethod));
    }

    public function knownHttpRequestMethodCodesProvider(): array
    {
        return array_map(function (string $code) { return [$this->createHttpRequestWithMethod($code)]; }, [
            HttpRequest::METHOD_GET,
            HttpRequest::METHOD_POST,
            HttpRequest::METHOD_PUT,
            HttpRequest::METHOD_DELETE,
            HttpRequest::METHOD_HEAD,
        ]);
    }

    public function testReturnsMethodNotAllowedHttpResponse()
    {
        $unknownMethodRequest = $this->createHttpRequestWithMethod('FOO');
        $response = (new UnknownHttpRequestMethodHandler())->process($unknownMethodRequest);
        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', $response->getBody());
    }
}
