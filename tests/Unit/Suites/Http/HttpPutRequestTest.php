<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPutRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpRequestBody
 */
class HttpPutRequestTest extends AbstractHttpRequestTest
{
    public function testPutRequestIsReturned()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::HTTP_PUT_REQUEST,
            $this->getStubHttpUrl(),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertInstanceOf(HttpPutRequest::class, $request);
        $this->assertInstanceOf(HttpRequest::class, $request);
    }

    public function testItReturnsTheRequestContentForNonMultipartFormDataRequests()
    {
        $headers = HttpHeaders::fromArray([]);
        $requestBody = HttpRequestBody::fromString('some-request-content');
        $request = HttpRequest::fromParameters(
            HttpRequest::HTTP_PUT_REQUEST,
            $this->getStubHttpUrl(),
            $headers,
            $requestBody
        );
        $this->assertSame('some-request-content', $request->getRawBody());
    }
}
