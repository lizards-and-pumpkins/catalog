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
    /**
     * @var HttpGetRequest
     */
    private $request;

    protected function setUp()
    {
        $stubHttpUrl = $this->getStubHttpUrl();
        $this->request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
    }

    public function testGetRequestIsReturned()
    {
        $this->assertInstanceOf(HttpGetRequest::class, $this->request);
    }

    public function testGetMethodNameIsReturned()
    {
        $result = $this->request->getMethod();
        $this->assertSame(HttpRequest::METHOD_GET, $result);
    }
}
