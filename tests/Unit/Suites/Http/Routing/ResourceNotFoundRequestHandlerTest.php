<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler
 */
class ResourceNotFoundRequestHandlerTest extends TestCase
{
    /**
     * @var ResourceNotFoundRequestHandler
     */
    private $requestHandler;

    public function setUp()
    {
        $this->requestHandler = new ResourceNotFoundRequestHandler();
    }

    public function testInstanceOfHttpResourceNotFoundResponseIsReturned()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->createMock(HttpRequest::class);
        $result = $this->requestHandler->process($stubRequest);

        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $result);
    }
}
