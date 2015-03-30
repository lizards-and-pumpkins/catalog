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

    /**
     * @test
     * @expectedException \Brera\Http\UnableToRouteRequestException
     * @expectedExceptionMessage Unable to route a request ""
     */
    public function itShouldThrowUnableToRouteRequestException()
    {
        $stubHttpRequest = $this->getStubHttpRequest();
        /* @var $stubContext Context|\PHPUnit_Framework_MockObject_MockObject */
        $stubContext = $this->getMock(Context::class);
        $this->routerChain->route($stubHttpRequest, $stubContext);
    }

    /**
     * @test
     */
    public function itShouldRouteARequest()
    {
        /* @var $stubHttpRouter HttpRouter|\PHPUnit_Framework_MockObject_MockObject */
        $stubHttpRouter = $this->getMock(HttpRouter::class);

        $stubHttpRequestHandler = $this->getMockBuilder(HttpRequestHandler::class)
            ->setMethods(['process'])
            ->getMock();

        $stubHttpRouter->expects($this->once())
            ->method('route')
            ->willReturn($stubHttpRequestHandler);

        $stubHttpRequest = $this->getStubHttpRequest();
        /* @var $stubContext Context|\PHPUnit_Framework_MockObject_MockObject */
        $stubContext = $this->getMock(Context::class);

        $this->routerChain->register($stubHttpRouter);

        $handler = $this->routerChain->route($stubHttpRequest, $stubContext);

        $this->assertNotNull($handler);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpRequest
     */
    private function getStubHttpRequest()
    {
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        return $stubHttpRequest;
    }
}
