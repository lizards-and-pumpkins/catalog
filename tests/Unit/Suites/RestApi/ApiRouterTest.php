<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\Exception\HeaderNotPresentException;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\ApiRouter
 * @uses   \LizardsAndPumpkins\Http\Routing\HttpRequestHandler
 */
class ApiRouterTest extends TestCase
{
    /**
     * @var ApiRouter
     */
    private $apiRouter;

    /**
     * @var ApiRequestHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubApiRequestHandlerLocator;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpRequest;

    protected function setUp()
    {
        $this->stubApiRequestHandlerLocator = $this->createMock(ApiRequestHandlerLocator::class);
        $this->apiRouter = new ApiRouter($this->stubApiRequestHandlerLocator);

        $this->stubHttpRequest = $this->createMock(HttpRequest::class);
    }

    public function testNullIsReturnedIfUrlIsNotLedByApiPrefix()
    {
        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('foo/bar');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfVersionFormatIsInvalid()
    {
        $this->stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/json');
        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testReturnsNullIfRequestHasNoAcceptHeader()
    {
        $this->stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(false);
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willThrowException(new HeaderNotPresentException);
        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('api');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfEndpointCodeIsNotSpecified()
    {
        $this->stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('api');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfApiRequestHandlerCanNotProcessRequest()
    {
        $stubApiRequestHandler = $this->createMock(ApiRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(false);

        $this->stubApiRequestHandlerLocator->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('api/foo');
        $this->stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testApiRequestHandlerIsReturned()
    {
        $stubApiRequestHandler = $this->createMock(ApiRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(true);

        $this->stubApiRequestHandlerLocator->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $this->stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $this->stubHttpRequest->method('getPathWithoutWebsitePrefix')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertInstanceOf(ApiRequestHandler::class, $result);
    }
}
