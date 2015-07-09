<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpGetRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpRequestBody
 */
class HttpGetRequestTest extends AbstractHttpRequestTest
{
    public function testGetRequestIsReturned()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters(
            'GET',
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }
}
