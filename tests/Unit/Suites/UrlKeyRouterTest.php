<?php


namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRouter;
use Brera\Http\HttpUrl;

class UrlKeyRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyRouter
     */
    private $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlKeyRequestHandlerBuilder
     */
    private $stubUrlKeyRequestHandlerBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlKeyRequestHandler
     */
    private $mockUrlKeyRequestHandler;

    public function setUp()
    {
        $this->stubUrlKeyRequestHandlerBuilder = $this->getMockBuilder(UrlKeyRequestHandlerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockUrlKeyRequestHandler = $this->getMockBuilder(UrlKeyRequestHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubUrlKeyRequestHandlerBuilder->expects($this->any())
            ->method('create')
            ->willReturn($this->mockUrlKeyRequestHandler);
        $this->router = new UrlKeyRouter($this->stubUrlKeyRequestHandlerBuilder);
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
        $stubEnvironment = $this->getMock(Environment::class);
        $this->assertNull($this->router->route($stubRequest, $stubEnvironment));
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
        $stubEnvironment = $this->getMock(Environment::class);
        $this->assertSame($this->mockUrlKeyRequestHandler, $this->router->route($stubRequest, $stubEnvironment));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequest()
    {
        $stubRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubRequest->expects($this->any())
            ->method('getUrl')
            ->willReturn(HttpUrl::fromString('http://example.com/'));
        return $stubRequest;
    }
}
