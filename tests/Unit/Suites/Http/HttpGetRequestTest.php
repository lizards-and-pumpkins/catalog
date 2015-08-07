<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpGetRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpPostRequest
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
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);

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
