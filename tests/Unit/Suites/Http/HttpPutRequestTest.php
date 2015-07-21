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
    /**
     * @var HttpPutRequest
     */
    private $request;

    protected function setUp()
    {
        $stubHttpUrl = $this->getStubHttpUrl();
        $this->request = HttpRequest::fromParameters(
            HttpRequest::METHOD_PUT,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
    }

    public function testPutRequestIsReturned()
    {
        $this->assertInstanceOf(HttpPutRequest::class, $this->request);
    }

    public function testPutMethodNameIsReturned()
    {
        $result = $this->request->getMethod();
        $this->assertSame(HttpRequest::METHOD_PUT, $result);
    }
}
