<?php

namespace Brera\Http;

use Brera\Context\Context;

/**
 * @covers Brera\Http\HttpRouterChain
 */
class HttpRouterChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRouterChain
     */
    private $routerChain;

    protected function setUp()
    {
        $this->routerChain = new HttpRouterChain();
    }

    public function testUnableToRouteRequestExceptionIsThrown()
    {
        $this->setExpectedException(UnableToRouteRequestException::class);
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);
        $this->routerChain->route($stubHttpRequest, $stubContext);
    }

    public function testRequestIsRouted()
    {
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);
        $stubHttpRequestHandler = $this->getMock(HttpRequestHandler::class);

        $mockHttpRouter = $this->getMock(HttpRouter::class);
        $mockHttpRouter->expects($this->once())
            ->method('route')
            ->willReturn($stubHttpRequestHandler);

        $this->routerChain->register($mockHttpRouter);

        $handler = $this->routerChain->route($stubHttpRequest, $stubContext);

        $this->assertNotNull($handler);
    }
}
