<?php

namespace Brera\Http;

use Brera\Context\Context;

/**
 * @covers \Brera\Http\ResourceNotFoundRouter
 */
class ResourceNotFoundRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfResourceNotFoundRequestHandlerIsReturned()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);
        $result = (new ResourceNotFoundRouter())->route($stubRequest, $stubContext);

        $this->assertInstanceOf(HttpRequestHandler::class, $result);
    }
}
