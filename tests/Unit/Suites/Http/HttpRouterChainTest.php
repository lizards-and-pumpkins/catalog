<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\UnableToRouteRequestException;

/**
 * @covers LizardsAndPumpkins\Http\HttpRouterChain
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
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $this->setExpectedException(UnableToRouteRequestException::class);

        $this->routerChain->route($stubHttpRequest);
    }

    public function testRequestIsRouted()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequestHandler = $this->getMock(HttpRequestHandler::class);

        /** @var HttpRouter|\PHPUnit_Framework_MockObject_MockObject $mockHttpRouter */
        $mockHttpRouter = $this->getMock(HttpRouter::class);
        $mockHttpRouter->expects($this->once())
            ->method('route')
            ->willReturn($stubHttpRequestHandler);

        $this->routerChain->register($mockHttpRouter);

        $handler = $this->routerChain->route($stubHttpRequest);

        $this->assertNotNull($handler);
    }
}
