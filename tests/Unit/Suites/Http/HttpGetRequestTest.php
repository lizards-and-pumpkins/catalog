<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpGetRequest
 * @covers \Brera\Http\HttpRequest
 * @uses \Brera\Http\HttpUrl
 */
class HttpGetRequestTest extends AbstractHttpRequestTest
{
    /**
     * @test
     */
    public function itShouldReturnAGetRequest()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters('GET', $stubHttpUrl);

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }
}
