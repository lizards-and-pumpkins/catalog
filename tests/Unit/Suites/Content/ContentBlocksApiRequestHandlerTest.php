<?php

namespace Brera\Content;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Content\ContentBlocksApiRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\Content\ContentBlockId
 * @uses   \Brera\Content\ContentBlockSource
 * @uses   \Brera\Content\UpdateContentBlockCommand
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 */
class ContentBlocksApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ContentBlocksApiRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->requestHandler = new ContentBlocksApiRequestHandler($this->mockCommandQueue);
        $this->mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testApiRequestHandlerIsExtended()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfMethodIsNotPut()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanNotBeProcessedIfUrlDoesNotContainContentBlockId()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/content_blocks');
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/content_blocks/foo');
        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testExceptionIsThrownIfContentBlockContentIsMissingInRequestBody()
    {
        $this->setExpectedException(ContentBlockContentIsMissingInRequestBodyException::class);
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfContentBlockContextIsMissingInRequestBody()
    {
        $this->setExpectedException(ContentBlockContextIsMissingInRequestBodyException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '']));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfContentBlockContextIsNotAnArray()
    {
        $this->setExpectedException(InvalidContentBlockContext::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '', 'context' => '']));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testUpdateContentBlockCommandIsEmitted()
    {
        $requestBody = ['content' => 'bar', 'context' => ['baz' => 'qux']];
        $this->mockRequest->method('getRawBody')->willReturn(json_encode($requestBody));
        $this->mockRequest->method('getUrl')->willReturn('http://example.com/api/content_blocks/foo');

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(UpdateContentBlockCommand::class));

        $this->requestHandler->process($this->mockRequest);
    }
}
