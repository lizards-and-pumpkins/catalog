<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock\RestApi;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 */
class ContentBlocksApiV1PutRequestHandlerTest extends TestCase
{
    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dummyDataPoolReader;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var ContentBlocksApiV1PutRequestHandler
     */
    private $requestHandler;

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
        $this->dummyDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->requestHandler = new ContentBlocksApiV1PutRequestHandler(
            $this->mockCommandQueue,
            $this->stubContextBuilder,
            $this->dummyDataPoolReader
        );
        $this->mockRequest = $this->createMock(HttpRequest::class);
    }

    public function testEmitsUpdateContentBlockCommandWithCurrentDataVersion()
    {
        $testVersion = 'foo-bar';

        $this->dummyDataPoolReader->method('getCurrentDataVersion')->willReturn($testVersion);

        $requestBody = [
            'content' => 'bar',
            'context' => ['baz' => 'qux'],
            'url_key' => 'foo',
        ];
        $this->mockRequest->method('getRawBody')->willReturn(json_encode($requestBody));

        $url = HttpUrl::fromString('http://example.com/api/content_blocks/foo_bar');
        $this->mockRequest->method('getUrl')->willReturn($url);

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->willReturnCallback(function (UpdateContentBlockCommand $command) use ($testVersion) {
                $context = $command->getContentBlockSource()->getContext();
                $this->assertArrayHasKey(DataVersion::CONTEXT_CODE, $context->debug());
                $this->assertSame($testVersion, $context->debug()[DataVersion::CONTEXT_CODE]);
            });

        $response = $this->requestHandler->process($this->mockRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
