<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpOptionsRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class HttpOptionsRequestTest extends TestCase
{
    public function testReturnsHttpOptionsRequestInstance()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_OPTIONS,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertInstanceOf(HttpOptionsRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testReturnsOptionsMethodCode()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_OPTIONS,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame(HttpRequest::METHOD_OPTIONS, $request->getMethod());
    }
}
