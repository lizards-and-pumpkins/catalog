<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPostRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpRequestBody
 */
class HttpPostRequestTest extends AbstractHttpRequestTest
{
    public function testPostRequestIsReturned()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters(
            'POST',
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertInstanceOf(HttpPostRequest::class, $result);
    }
}
