<?php

namespace Brera;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

/**
 * @covers \Brera\PageTemplatesApiV1PutRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\PageTemplateWasUpdatedDomainEvent
 */
class PageTemplatesApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var PageTemplatesApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        /** @var RootSnippetSourceListBuilder|\PHPUnit_Framework_MockObject_MockObject $stubRootSnippetSourceListBuilder */
        $stubRootSnippetSourceListBuilder = $this->getMock(RootSnippetSourceListBuilder::class, [], [], '', false);
        $stubRootSnippetSourceListBuilder->method('createFromXml')->willReturn($stubRootSnippetSourceList);

        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->requestHandler = new PageTemplatesApiV1PutRequestHandler(
            $stubRootSnippetSourceListBuilder,
            $this->mockDomainEventQueue
        );

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

    public function testRequestCanNotBeProcessedIfUrlDoesNotContainRootSnippetId()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/page_templates');
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/page_templates/foo');
        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testUpdateContentBlockCommandIsEmitted()
    {
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/page_templates/foo');

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(PageTemplateWasUpdatedDomainEvent::class));

        $this->requestHandler->process($this->mockRequest);
    }
}
