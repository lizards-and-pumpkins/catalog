<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\HttpPostRequest
 * @covers \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpGetRequest
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
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);

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
