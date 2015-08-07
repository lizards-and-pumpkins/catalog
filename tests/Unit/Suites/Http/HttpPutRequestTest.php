<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPutRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpGetRequest
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpPostRequest
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
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);

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
