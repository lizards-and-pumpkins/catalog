<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpTraceRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class HttpTraceRequestTest extends TestCase
{
    public function testReturnsHttpTraceRequestInstance()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_TRACE,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertInstanceOf(HttpTraceRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testReturnsTraceMethodCode()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_TRACE,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame(HttpRequest::METHOD_TRACE, $request->getMethod());
    }
}
