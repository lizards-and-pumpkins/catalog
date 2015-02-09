<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPostRequest
 * @covers \Brera\Http\HttpRequest
 * @uses \Brera\Http\HttpUrl
 */
class HttpPostRequestTest extends AbstractHttpRequest
{
    /**
     * @test
     */
    public function itShouldReturnAPostRequest()
    {
        $stubHttpUrl = $this->getStubHttpUrl();

        $result = HttpRequest::fromParameters('POST', $stubHttpUrl);

        $this->assertInstanceOf(HttpPostRequest::class, $result);
    }
}
