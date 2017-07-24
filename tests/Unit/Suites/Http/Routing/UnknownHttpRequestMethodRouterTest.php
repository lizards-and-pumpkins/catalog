<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodRouter
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class UnknownHttpRequestMethodRouterTest extends TestCase
{
    /**
     * @var HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestHandler;

    /**
     * @var UnknownHttpRequestMethodRouter
     */
    private $router;

    public function setUp()
    {
        $this->mockRequestHandler = $this->createMock(HttpRequestHandler::class);
        $this->router = new UnknownHttpRequestMethodRouter($this->mockRequestHandler);
    }

    public function testHttpRouterInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRouter::class, $this->router);
    }

    public function testNullIsReturnedIfRequestHandlerIsUnableToProcessRequest()
    {
        $stubRequest = $this->createStubRequest();
        $this->mockRequestHandler->expects($this->once())->method('canProcess')->willReturn(false);

        $this->assertNull($this->router->route($stubRequest));
    }

    public function testRequestHandlerIsReturnedIfRequestHandlerCanProcessRequest()
    {
        $stubRequest = $this->createStubRequest();
        $this->mockRequestHandler->expects($this->once())->method('canProcess')->willReturn(true);

        $this->assertSame($this->mockRequestHandler, $this->router->route($stubRequest));
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubRequest() : HttpRequest
    {
        $stubRequest = $this->createMock(HttpRequest::class);
        $stubRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/'));

        return $stubRequest;
    }
}
