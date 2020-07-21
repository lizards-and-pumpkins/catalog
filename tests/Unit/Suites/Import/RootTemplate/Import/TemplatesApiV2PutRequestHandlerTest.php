<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate\Import;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\Exception\InvalidTemplateApiRequestBodyException;
use LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class TemplatesApiV2PutRequestHandlerTest extends TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var TemplatesApiV2PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
        $this->requestHandler = new TemplatesApiV2PutRequestHandler($this->mockCommandQueue);
        
        $this->mockRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsHttpRequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
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

    public function testThrowsExceptionIfRequestBodyIsNotValidJson()
    {
        $this->expectException(InvalidTemplateApiRequestBodyException::class);
        $this->expectExceptionMessage('The request body is not valid JSON: Syntax error');
        
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn('this is not JSON!');
        
        $this->requestHandler->process($this->mockRequest);
    }

    public function testThrowsExceptionIfRequestDoesNotContainADataVersion()
    {
        $this->expectException(InvalidTemplateApiRequestBodyException::class);
        $this->expectExceptionMessage('The API request is missing the target data_version.');
        
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['content' => 'foo']));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testDoesNotThrowExceptionIfRequestDoesNotContainContent()
    {
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['data_version' => 'foo']));

        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (UpdateTemplateCommand $command) {
                $this->assertSame('', $command->getTemplateContent());
            });

        $this->requestHandler->process($this->mockRequest);
    }

    public function testEmitsUpdateTemplateCommand()
    {
        $testVersionString = 'foo';
        $testContent = 'some raw template related data';
        $this->mockRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/templates/foo'));
        $this->mockRequest->method('getRawBody')->willReturn(json_encode([
            'content' => $testContent,
            'data_version' => $testVersionString
        ]));

        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (UpdateTemplateCommand $command) use ($testVersionString, $testContent) {
                $this->assertSame($testContent, $command->getTemplateContent());
                $this->assertEquals($testVersionString, $command->getDataVersion());
            });

        $response = $this->requestHandler->process($this->mockRequest);
        
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
