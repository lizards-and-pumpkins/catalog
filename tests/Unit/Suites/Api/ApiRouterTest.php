<?php

namespace Brera\Api;

use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\Api\ApiRouter
 * @uses   \Brera\Http\HttpRequestHandler
 */
class ApiRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiRouter
     */
    private $apiRouter;

    /**
     * @var ApiRequestHandlerChain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubApiRequestHandlerChain;

    /**
     * @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrl;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpRequest;

    protected function setUp()
    {
        $this->stubApiRequestHandlerChain = $this->getMock(ApiRequestHandlerChain::class);
        $this->apiRouter = new ApiRouter($this->stubApiRequestHandlerChain);

        $this->stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubHttpRequest->method('getUrl')->willReturn($this->stubUrl);
    }

    public function testNullIsReturnedIfUrlIsNotLedByApiPrefix()
    {
        $this->stubUrl->method('getPathRelativeToWebFront')->willReturn('foo/bar');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfVersionFormatIsInvalid()
    {
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/json');
        $this->stubUrl->method('getPathRelativeToWebFront')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfEndpointCodeIsNotSpecified()
    {
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/vnd.brera.foo.v1+json');
        $this->stubUrl->method('getPathRelativeToWebFront')->willReturn('api');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfApiRequestHandlerCanNotProcessRequest()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(false);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubUrl->expects($this->once())->method('getPathRelativeToWebFront')->willReturn('api/foo');
        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/vnd.brera.foo.v1+json');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertNull($result);
    }

    public function testApiRequestHandlerIsReturned()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(true);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubHttpRequest->method('getHeader')->with('Accept')->willReturn('application/vnd.brera.foo.v1+json');
        $this->stubUrl->method('getPathRelativeToWebFront')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
