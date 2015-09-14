<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\TemplateWasUpdatedDomainEvent
 */
class TemplatesApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var TemplatesApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->mockDomainEventQueue = $this->getMock(Queue::class);
        $this->requestHandler = new TemplatesApiV1PutRequestHandler($this->mockDomainEventQueue);

        $this->mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testApiRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfMethodIsNotPut()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanNotBeProcessedIfUrlDoesNotContainTemplateId()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/templates');

        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/templates/foo');

        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testTemplateWasUpdatedDomainEventIsEmitted()
    {
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/templates/foo');

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(TemplateWasUpdatedDomainEvent::class));

        $this->requestHandler->process($this->mockRequest);
    }
}
