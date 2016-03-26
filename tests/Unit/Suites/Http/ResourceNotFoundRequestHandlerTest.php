<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Routing\Exception\HttpResourceNotFoundResponse;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler
 */
class ResourceNotFoundRequestHandlerTest extends \PHPUnit_Framework_TestCase
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
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $result = $this->requestHandler->process($stubRequest);

        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $result);
    }

    public function testTrueIsReturnedForEveryRequest()
    {
        $mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->assertTrue($this->requestHandler->canProcess($mockRequest));
    }
}
