<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\GenericHttpRouter
 * @uses   \Brera\Http\HttpUrl
 */
class GenericHttpRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestHandler;

    /**
     * @var GenericHttpRouter
     */
    private $router;

    public function setUp()
    {
        $this->mockRequestHandler = $this->getMock(HttpRequestHandler::class, [], [], '', false);
        $this->router = new GenericHttpRouter($this->mockRequestHandler);
    }

    public function testHttpRouterInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRouter::class, $this->router);
    }

    public function testNullIsReturnedIfRequestHandlerIsUnableToProcessRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getStubRequest();
        $this->mockRequestHandler->expects($this->once())->method('canProcess')->willReturn(false);

        $this->assertNull($this->router->route($stubRequest));
    }

    public function testRequestHandlerIsReturnedIfRequestHandlerCanProcessRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getStubRequest();
        $this->mockRequestHandler->expects($this->once())->method('canProcess')->willReturn(true);

        $this->assertSame($this->mockRequestHandler, $this->router->route($stubRequest));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequest()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/'));

        return $stubRequest;
    }
}
