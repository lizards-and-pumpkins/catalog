<?php

namespace LizardsAndPumpkins\Http;

/**
 * @covers \LizardsAndPumpkins\Http\HttpPutRequest
 * @covers \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\HttpGetRequest
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpPostRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 */
class HttpPutRequestTest extends AbstractHttpRequestTest
{
    /**
     * @var HttpPutRequest
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);

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
