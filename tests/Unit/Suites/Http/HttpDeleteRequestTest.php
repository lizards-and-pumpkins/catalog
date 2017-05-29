<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use PHPUnit\Framework\TestCase;

class HttpDeleteRequestTest extends TestCase
{
    public function testReturnsAHttpDeleteRequestInstance()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_DELETE,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertInstanceOf(HttpDeleteRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }
    
    public function testReturnsDeleteMethodCode()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_DELETE,
            HttpUrl::fromString('https://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame(HttpRequest::METHOD_DELETE, $request->getMethod());
    }
}
