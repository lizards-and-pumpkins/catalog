<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRouter;
use Brera\Http\HttpUrl;

abstract class AbstractRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewRouter
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductDetailViewRequestHandlerBuilder
     */
    protected $mockRequestHandlerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProductDetailViewRequestHandler
     */
    protected $mockRequestHandler;

    /**
     * @test
     */
    public function itShouldBeAHttpRouter()
    {
        $this->assertInstanceOf(HttpRouter::class, $this->router);
    }

    /**
     * @test
     */
    public function itShouldReturnNullIfTheRequestHandlerIsUnableToProcessRequest()
    {
        $this->mockRequestHandler->expects($this->once())
            ->method('canProcess')
            ->willReturn(false);
        $stubRequest = $this->getStubRequest();
        $stubContext = $this->getMock(Context::class);
        $this->assertNull($this->router->route($stubRequest, $stubContext));
    }

    /**
     * @test
     */
    public function itShouldReturnTheRequestHandlerIfItIsAbleToProcessRequest()
    {
        $this->mockRequestHandler->expects($this->once())
            ->method('canProcess')
            ->willReturn(true);
        $stubRequest = $this->getStubRequest();
        $stubContext = $this->getMock(Context::class);
        $this->assertSame($this->mockRequestHandler, $this->router->route($stubRequest, $stubContext));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequest()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequest->expects($this->any())
            ->method('getUrl')
            ->willReturn(HttpUrl::fromString('http://example.com/'));

        return $stubRequest;
    }
}
