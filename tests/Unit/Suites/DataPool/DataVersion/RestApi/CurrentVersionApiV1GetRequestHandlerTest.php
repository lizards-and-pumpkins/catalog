<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion\RestApi;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpGetRequest
 * @uses   \LizardsAndPumpkins\Http\HttpPostRequest
 * @uses   \LizardsAndPumpkins\Http\HttpPutRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequest
 * @uses   \LizardsAndPumpkins\Http\HttpRequestBody
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 */
class CurrentVersionApiV1GetRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    private function createHandler(): CurrentVersionApiV1GetRequestHandler
    {
        return new CurrentVersionApiV1GetRequestHandler($this->mockDataPoolReader);
    }

    private function createHttpRequest($requestMethod): HttpRequest
    {
        return HttpRequest::fromParameters(
            $requestMethod,
            HttpUrl::fromString('https://example.com/api/current_version'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
    }

    protected function setUp()
    {
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
    }
    
    public function testInheritsFromApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->createHandler());
    }

    /**
     * @dataProvider nonGetHttpRequestMethodProvider
     */
    public function testDoesNotHandleNonGetRequests(string $nonGetRequestMethod)
    {
        $request = $this->createHttpRequest($nonGetRequestMethod);
        $this->assertFalse($this->createHandler()->canProcess($request));
    }

    public function nonGetHttpRequestMethodProvider(): array
    {
        return [
            'post'   => [HttpRequest::METHOD_POST],
            'put'    => [HttpRequest::METHOD_PUT],
        ];
    }

    public function testHandlesGetRequests()
    {
        $request = $this->createHttpRequest(HttpRequest::METHOD_GET);
        $this->assertTrue($this->createHandler()->canProcess($request));
    }

    public function testRespondsWithCurrentAndPreviousDataVersion()
    {
        $this->mockDataPoolReader->method('getCurrentDataVersion')->willReturn('foo');
        $this->mockDataPoolReader->method('getPreviousDataVersion')->willReturn('bar');
        
        $expected = json_encode([
            'data' => [
                'current_version' => 'foo',
                'previous_version' => 'bar',
            ]
        ]);

        $request = $this->createHttpRequest(HttpRequest::METHOD_GET);
        $this->assertSame($expected, $this->createHandler()->process($request)->getBody());
    }
}
