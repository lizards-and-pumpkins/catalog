<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRouter;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\UrlKeyRouter
 * @uses \Brera\Http\HttpUrl
 */
class UrlKeyRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyRouter
     */
    private $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlKeyRequestHandlerBuilder
     */
    private $mockUrlKeyRequestHandlerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlKeyRequestHandler
     */
    private $mockUrlKeyRequestHandler;

    public function setUp()
    {
        $this->mockUrlKeyRequestHandler = $this->getMock(UrlKeyRequestHandler::class, [], [], '', false);

        $this->mockUrlKeyRequestHandlerBuilder = $this->getMock(UrlKeyRequestHandlerBuilder::class, [], [], '', false);
        $this->mockUrlKeyRequestHandlerBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockUrlKeyRequestHandler);

        $this->router = new UrlKeyRouter($this->mockUrlKeyRequestHandlerBuilder);
    }

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
        $this->mockUrlKeyRequestHandler->expects($this->once())
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
        $this->mockUrlKeyRequestHandler->expects($this->once())
            ->method('canProcess')
            ->willReturn(true);
        $stubRequest = $this->getStubRequest();
        $stubContext = $this->getMock(Context::class);
        $this->assertSame($this->mockUrlKeyRequestHandler, $this->router->route($stubRequest, $stubContext));
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
