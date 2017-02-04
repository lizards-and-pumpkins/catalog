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
        $stubRequest = $this->createMock(HttpRequest::class);
        $result = $this->requestHandler->process($stubRequest);

        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $result);
    }

    public function testTrueIsReturnedForEveryRequest()
    {
        $mockRequest = $this->createMock(HttpRequest::class);
        $this->assertTrue($this->requestHandler->canProcess($mockRequest));
    }
}
