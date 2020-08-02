<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\Exception\UnableToRouteRequestException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\HttpRouterChain
 */
class HttpRouterChainTest extends TestCase
{
    /**
     * @var HttpRouterChain
     */
    private $routerChain;

    final protected function setUp(): void
    {
        $this->routerChain = new HttpRouterChain();
    }

    public function testUnableToRouteRequestExceptionIsThrown(): void
    {
        /** @var HttpRequest|MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        $this->expectException(UnableToRouteRequestException::class);

        $this->routerChain->route($stubHttpRequest);
    }

    public function testRequestIsRouted(): void
    {
        /** @var HttpRequest|MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);

        /** @var HttpRouter|MockObject $mockHttpRouter */
        $mockHttpRouter = $this->createMock(HttpRouter::class);
        $mockHttpRouter->expects($this->once())
            ->method('route')
            ->willReturn($stubHttpRequestHandler);

        $this->routerChain->register($mockHttpRouter);

        $handler = $this->routerChain->route($stubHttpRequest);

        $this->assertNotNull($handler);
    }
}
