<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpConnectRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class HttpConnectRequestTest extends TestCase
{
    public function testReturnsHttpConnectRequestInstance()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_CONNECT,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertInstanceOf(HttpConnectRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testReturnsConnectMethodCode()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_CONNECT,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame(HttpRequest::METHOD_CONNECT, $request->getMethod());
    }
}
