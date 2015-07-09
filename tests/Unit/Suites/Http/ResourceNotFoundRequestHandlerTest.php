<?php

namespace Brera\Http;

/**
 * @covers \Brera\Http\ResourceNotFoundRequestHandler
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
        $this->assertTrue($this->requestHandler->canProcess());
    }
}
