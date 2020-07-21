<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockBodyIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\ContentBlockContextIsMissingInRequestBodyException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockContextException;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\InvalidContentBlockUrlKey;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\Exception\MissingContentBlockDataVersionException;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class ContentBlocksApiV2PutRequestHandlerTest extends TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ContentBlocksApiV2PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;
    
    protected function setUp()
    {
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $this->stubContextBuilder->method('createContext')->willReturnCallback(function (array $parts) {
            $stubContext = $this->getMockBuilder(Context::class)
                ->setMethods(array_merge(get_class_methods(Context::class), ['debug']))
                ->getMock();
            $stubContext->method('debug')->willReturn($parts);
            return $stubContext;
        });
        $this->requestHandler = new ContentBlocksApiV2PutRequestHandler(
            $this->mockCommandQueue,
            $this->stubContextBuilder
        );
        $this->mockRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsHttpRequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testCanNotProcessRequestIfMethodIsNotPut()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testCanNotProcessRequestIfUrlDoesNotContainContentBlockId()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);

        $url = HttpUrl::fromString('http://example.com/api/content_blocks');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testCanProcessRequestIfValid()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testThrowsExceptionIfContentBlockContentIsMissingInRequestBody()
    {
        $this->expectException(ContentBlockBodyIsMissingInRequestBodyException::class);
        $this->expectExceptionMessage('Content block content is missing in request body.');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode([]));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testThrowsExceptionIfContentBlockContextIsMissingInRequestBody()
    {
        $this->expectException(ContentBlockContextIsMissingInRequestBodyException::class);
        $this->expectExceptionMessage('Content block context is missing in request body.');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '']));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testThrowsExceptionIfContentBlockContextIsNotAnArray()
    {
        $this->expectException(InvalidContentBlockContextException::class);
        $this->expectExceptionMessage('Content block context supposed to be an array, got string.');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => '', 'context' => '']));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testThrowsExceptionIfContentBlockUrlKeyIsInvalid()
    {
        $this->expectException(InvalidContentBlockUrlKey::class);
        $this->expectExceptionMessage('Content block URL key must be a string, got integer.');

        $this->mockRequest->method('getRawBody')
            ->willReturn(json_encode(['content' => '', 'context' => [], 'url_key' => 1]));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testThrowsExceptionIfDataVersionIsMissing()
    {
        $this->expectException(MissingContentBlockDataVersionException::class);
        $this->expectExceptionMessage('The content block data version must be specified.');

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo_bar');
        $this->mockRequest->method('getUrl')->willReturn($url);
        $this->mockRequest->method('getRawBody')
            ->willReturn(json_encode(['content' => '', 'context' => []]));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testValidatesTheDataVersion()
    {
        $this->expectException(EmptyVersionException::class);
        $this->expectExceptionMessage('The specified version is empty.');

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo_bar');
        $this->mockRequest->method('getUrl')->willReturn($url);
        $this->mockRequest->method('getRawBody')
            ->willReturn(json_encode(['content' => '', 'context' => [], 'data_version' => '']));

        $this->requestHandler->process($this->mockRequest);

    }

    public function testEmitsUpdateContentBlockCommand()
    {
        $testVersion = 'foo-bar';
        $requestBody = [
            'content' => 'bar',
            'context' => ['baz' => 'qux'],
            'url_key' => 'foo',
            'data_version' => $testVersion
        ];
        $this->mockRequest->method('getRawBody')->willReturn(json_encode($requestBody));

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo_bar');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (UpdateContentBlockCommand $command) use ($testVersion) {
                $this->assertEquals('foo_bar', $command->getContentBlockSource()->getContentBlockId());
                $this->assertSame(['url_key' => 'foo'], $command->getContentBlockSource()->getKeyGeneratorParams());
                $this->assertSame('bar', $command->getContentBlockSource()->getContent());
                $context = $command->getContentBlockSource()->getContext();
                $this->assertInstanceOf(Context::class, $context);
                $this->assertArrayHasKey(DataVersion::CONTEXT_CODE, $context->debug());
                $this->assertSame($testVersion, $context->debug()[DataVersion::CONTEXT_CODE]);
            });

        $response = $this->requestHandler->process($this->mockRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
