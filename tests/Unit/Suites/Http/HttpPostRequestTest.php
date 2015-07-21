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
    /**
     * @var HttpPostRequest
     */
    private $request;

    protected function setUp()
    {
        $stubHttpUrl = $this->getStubHttpUrl();
        $this->request = HttpRequest::fromParameters(
            HttpRequest::METHOD_POST,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
    }

    public function testPostRequestIsReturned()
    {
        $this->assertInstanceOf(HttpPostRequest::class, $this->request);
    }

    public function testPostMethodNameIsReturned()
    {
        $result = $this->request->getMethod();
        $this->assertSame(HttpRequest::METHOD_POST, $result);
    }
}
