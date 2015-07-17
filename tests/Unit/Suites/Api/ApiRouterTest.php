<?php

namespace Brera\Api;

use Brera\Context\Context;
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

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    protected function setUp()
    {
        $this->stubApiRequestHandlerChain = $this->getMock(ApiRequestHandlerChain::class);
        $this->apiRouter = new ApiRouter($this->stubApiRequestHandlerChain);

        $this->stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubHttpRequest->expects($this->once())->method('getUrl')->willReturn($this->stubUrl);

        $this->stubContext = $this->getMock(Context::class);
    }

    public function testNullIsReturnedIfUrlIsNotLedByApiPrefix()
    {
        $this->stubUrl->expects($this->once())->method('getPath')->willReturn('foo/bar');
        $result = $this->apiRouter->route($this->stubHttpRequest, $this->stubContext);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfVersionFormatIsInvalid()
    {
        $this->stubUrl->expects($this->once())->method('getPath')->willReturn('api/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest, $this->stubContext);

        $this->assertNull($result);
    }

    public function testNullIsReturnedIfApiRequestHandlerCanNotProcessRequest()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(false);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubUrl->expects($this->once())->method('getPath')->willReturn('api/v1/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest, $this->stubContext);

        $this->assertNull($result);
    }

    public function testApiRequestHandlerIsReturned()
    {
        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);
        $stubApiRequestHandler->method('canProcess')->willReturn(true);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $this->stubUrl->expects($this->once())->method('getPath')->willReturn('api/v1/foo');
        $result = $this->apiRouter->route($this->stubHttpRequest, $this->stubContext);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
