<?php

namespace LizardsAndPumpkins\Http;

/**
 * @covers \LizardsAndPumpkins\Http\HttpPostRequest
 * @covers \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpGetRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpPostRequestTest extends AbstractHttpRequestTest
{
    /**
     * @var HttpPostRequest
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);

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
