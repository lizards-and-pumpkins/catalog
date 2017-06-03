<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\HttpUnknownMethodRequest
 * @covers \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpGetRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpPostRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpUnknownMethodRequestTest extends TestCase
{
    public function testReturnsHttpUnknownRequestMethodInstance()
    {
        $unknownRequestMethod = 'FOO';
        $request = HttpRequest::fromParameters(
            $unknownRequestMethod,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertInstanceOf(HttpUnknownMethodRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testReturnsTheGivenMethodCode()
    {
        $unknownRequestMethod = 'FOO';
        $request = HttpRequest::fromParameters(
            $unknownRequestMethod,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame($unknownRequestMethod, $request->getMethod());
    }
}
