<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 */
class TemplatesApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
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
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
        $this->requestHandler = new TemplatesApiV1PutRequestHandler($this->mockDomainEventQueue);

        $this->mockRequest = $this->createMock(HttpRequest::class);
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
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates'));

        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));

        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testTemplateWasUpdatedDomainEventIsEmitted()
    {
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn('Raw Request Body');

        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $response = $this->requestHandler->process($this->mockRequest);
        
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
