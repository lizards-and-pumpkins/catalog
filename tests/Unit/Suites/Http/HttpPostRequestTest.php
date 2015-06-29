<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPostRequest
 * @covers \Brera\Http\HttpRequest
 * @uses \Brera\Http\HttpUrl
 */
class HttpPostRequestTest extends AbstractHttpRequestTest
{
    public function testPostRequestIsReturned()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters('POST', $stubHttpUrl);

        $this->assertInstanceOf(HttpPostRequest::class, $result);
    }
}
