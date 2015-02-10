<?php

namespace Brera\Http;

use Brera\Environment\Environment;

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
        $stubEnvironment = $this->getMock(Environment::class);
        $this->routerChain->route($stubHttpRequest, $stubEnvironment);
    }

    /**
     * @test
     */
    public function itShouldRouteARequest()
    {
        $stubHttpRouter = $this->getMock(HttpRouter::class);

        $stubHttpRequestHandler = $this->getMock(HttpRequestHandler::class, ['process']);

        $stubHttpRouter->expects($this->once())
        ->method('route')
        ->willReturn($stubHttpRequestHandler);

        $stubHttpRequest = $this->getStubHttpRequest();
        
        $stubEnvironment = $this->getMock(Environment::class);

        $this->routerChain->register($stubHttpRouter);
        $handler = $this->routerChain->route($stubHttpRequest, $stubEnvironment);

        $this->assertNotNull($handler);
    }

    private function getStubHttpRequest()
    {
        $stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
        ->disableOriginalConstructor()
        ->getMock();

        return $stubHttpRequest;
    }
}
