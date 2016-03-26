<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRouter
 */
class ResourceNotFoundRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfResourceNotFoundRequestHandlerIsReturned()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $result = (new ResourceNotFoundRouter())->route($stubRequest);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
