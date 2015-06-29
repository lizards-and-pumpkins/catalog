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

    protected function setUp()
    {
        $this->stubApiRequestHandlerChain = $this->getMock(ApiRequestHandlerChain::class);
        $this->apiRouter = new ApiRouter($this->stubApiRequestHandlerChain);
    }

    public function testNullIsReturnedIfUrlIsNotLedByApiPrefix()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('foo/bar');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);
        
        $stubContext = $this->getMock(Context::class);

        $this->assertNull($this->apiRouter->route($stubHttpRequest, $stubContext));
    }

    public function testNullIsReturnedIfNoApiRequestHandlerIsFound()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('api/foo');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);

        $stubContext = $this->getMock(Context::class);

        $this->assertNull($this->apiRouter->route($stubHttpRequest, $stubContext));
    }

    public function testApiRequestHandlerIsReturned()
    {
        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('api/foo');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);

        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $stubContext = $this->getMock(Context::class);
        
        $result = $this->apiRouter->route($stubHttpRequest, $stubContext);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubHttpRequest()
    {
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        return $stubHttpRequest;
    }
}
