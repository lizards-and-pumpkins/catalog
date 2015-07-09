<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPutRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 */
class HttpPutRequestTest extends AbstractHttpRequestTest
{
    public function testPostRequestIsReturned()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters('PUT', $stubHttpUrl, HttpHeaders::fromArray([]));

        $this->assertInstanceOf(HttpPutRequest::class, $result);
        $this->assertInstanceOf(HttpRequest::class, $result);
    }

    public function itShouldReturnAnEmptyStringForMultipartFormdataRequests()
    {

    }
}
