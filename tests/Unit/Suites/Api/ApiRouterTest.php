<?php

namespace Brera\Api;

use Brera\Environment\Environment;
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

    /**
     * @test
     */
    public function itShouldReturnNullIfUrlIsNotLeadByApiPrefix()
    {
        $stubUrl = $this->getMockBuilder(HttpUrl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('foo/bar');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);
        
        $stubEnvironment = $this->getMock(Environment::class);

        $this->assertNull($this->apiRouter->route($stubHttpRequest, $stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturnNullIfNoApiRequestHandlerFound()
    {
        $stubUrl = $this->getMockBuilder(HttpUrl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('api/foo');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);

        $stubEnvironment = $this->getMock(Environment::class);
        
        $this->assertNull($this->apiRouter->route($stubHttpRequest, $stubEnvironment));
    }

    /**
     * @test
     */
    public function itShouldReturnApiRequestHandler()
    {
        $stubUrl = $this->getMockBuilder(HttpUrl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubUrl->expects($this->once())
            ->method('getPath')
            ->willReturn('api/foo');

        $stubHttpRequest = $this->getStubHttpRequest();
        $stubHttpRequest->expects($this->once())
            ->method('getUrl')
            ->willReturn($stubUrl);

        $stubApiRequestHandler = $this->getMock(HttpRequestHandler::class, ['process']);

        $this->stubApiRequestHandlerChain->expects($this->once())
            ->method('getApiRequestHandler')
            ->willReturn($stubApiRequestHandler);

        $stubEnvironment = $this->getMock(Environment::class);
        
        $result = $this->apiRouter->route($stubHttpRequest, $stubEnvironment);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubHttpRequest()
    {
        $stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $stubHttpRequest;
    }
}
