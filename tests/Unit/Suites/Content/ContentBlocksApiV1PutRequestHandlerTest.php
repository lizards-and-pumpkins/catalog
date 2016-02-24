<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Content\Exception\ContentBlockBodyIsMissingInRequestBodyException;
use LizardsAndPumpkins\Content\Exception\ContentBlockContextIsMissingInRequestBodyException;
use LizardsAndPumpkins\Content\Exception\InvalidContentBlockContextException;
use LizardsAndPumpkins\Content\Exception\InvalidContentBlockUrlKey;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Content\ContentBlockId
 * @uses   \LizardsAndPumpkins\Content\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Content\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class ContentBlocksApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ContentBlocksApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->requestHandler = new ContentBlocksApiV1PutRequestHandler($this->mockCommandQueue);
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
        $this->expectException(ContentBlockBodyIsMissingInRequestBodyException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode([]));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfContentBlockContextIsMissingInRequestBody()
    {
        $this->expectException(ContentBlockContextIsMissingInRequestBodyException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '']));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfContentBlockContextIsNotAnArray()
    {
        $this->expectException(InvalidContentBlockContextException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '', 'context' => '']));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfContentBlockUrlKeyIsInvalid()
    {
        $this->expectException(InvalidContentBlockUrlKey::class);
        $this->mockRequest->method('getRawBody')
            ->willReturn(json_encode(['content' => '', 'context' => [], 'url_key' => 1]));
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
