<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class ContentBlocksApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
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
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
        $this->requestHandler = new ContentBlocksApiV1PutRequestHandler($this->mockCommandQueue);
        $this->mockRequest = $this->createMock(HttpRequest::class);
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

        $url = HttpUrl::fromString('http://example.com/api/content_blocks');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testExceptionIsThrownIfContentBlockContentIsMissingInRequestBody()
    {
        $this->mockRequest->method('getRawBody')->willReturn(json_encode([]));

        $response = $this->requestHandler->process($this->mockRequest);
        $expectedResponseBody = json_encode(['error' => 'Content block content is missing in request body.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testExceptionIsThrownIfContentBlockContextIsMissingInRequestBody()
    {
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '']));

        $response = $this->requestHandler->process($this->mockRequest);
        $expectedResponseBody = json_encode(['error' => 'Content block context is missing in request body.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testExceptionIsThrownIfContentBlockContextIsNotAnArray()
    {
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '', 'context' => '']));

        $response = $this->requestHandler->process($this->mockRequest);
        $expectedResponseBody = json_encode(['error' => 'Content block context supposed to be an array, got string.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testExceptionIsThrownIfContentBlockUrlKeyIsInvalid()
    {
        $this->mockRequest->method('getRawBody')
            ->willReturn(json_encode(['content' => '', 'context' => [], 'url_key' => 1]));

        $response = $this->requestHandler->process($this->mockRequest);
        $expectedResponseBody = json_encode(['error' => 'Content block URL key must be a string, got integer.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testUpdateContentBlockCommandIsEmitted()
    {
        $requestBody = ['content' => 'bar', 'context' => ['baz' => 'qux']];
        $this->mockRequest->method('getRawBody')->willReturn(json_encode($requestBody));

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(UpdateContentBlockCommand::class));

        $response = $this->requestHandler->process($this->mockRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
