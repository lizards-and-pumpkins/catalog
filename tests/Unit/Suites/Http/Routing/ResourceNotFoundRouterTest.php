<?php

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;

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
