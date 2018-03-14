<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\UnableToProcessContentBlockApiGetRequestException;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockApiV2GetRequestHandler
 * @uses    \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses    \LizardsAndPumpkins\Http\HttpHeaders
 * @uses    \LizardsAndPumpkins\Http\HttpUrl
 */
class ContentBlockApiV2GetRequestHandlerTest extends TestCase
{
    /**
     * @var ContentBlockApiV2GetRequestHandler
     */
    private $handler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var ContentBlockService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockService;

    private function prepareValidContentBlock(string $blockName, string $blockContents)
    {
        $this->stubRequest->method('getUrl')
            ->willReturn(HttpUrl::fromString(sprintf('http://example.com/api/content_blocks/%s/', $blockName)));

        $this->stubContentBlockService->method('getContentBlock')->with($blockName)->willReturn($blockContents);
    }

    final protected function setUp()
    {
        $this->stubContentBlockService = $this->createMock(ContentBlockService::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $dummyContextBuilder */
        $dummyContextBuilder = $this->createMock(ContextBuilder::class);

        $this->handler = new ContentBlockApiV2GetRequestHandler($this->stubContentBlockService, $dummyContextBuilder);

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsHttpRequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->handler);
    }

    public function testCanNotProcessIfContentBlockIdIsMissing()
    {
        $this->stubRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/api/content_blocks/'));

        $this->assertFalse($this->handler->canProcess($this->stubRequest));
    }

    /**
     * @dataProvider emptyBlockIdProvider
     * @param string $emptyBlockId
     */
    public function testCanNotProcessIfContentBlockIdIsEmpty(string $emptyBlockId)
    {
        $this->stubRequest->method('getUrl')
            ->willReturn(HttpUrl::fromString(sprintf('http://example.com/api/content_blocks/%s/', $emptyBlockId)));

        $this->assertFalse($this->handler->canProcess($this->stubRequest));
    }

    public function emptyBlockIdProvider(): array
    {
        return [[''], ['%20']];
    }

    public function testCanProcess()
    {
        $this->prepareValidContentBlock('block_id', 'block_content');

        $this->assertTrue($this->handler->canProcess($this->stubRequest));
    }

    public function testThrowsExceptionDuringAttemptToProcessInvalidRequest()
    {
        $this->expectException(UnableToProcessContentBlockApiGetRequestException::class);

        $this->stubRequest->method('hasQueryParameter')->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
            ->willReturn(false);

        $this->handler->process($this->stubRequest);
    }

    public function testReturnsHttpResponse()
    {
        $this->prepareValidContentBlock('block_id', 'block_content');

        $this->assertInstanceOf(HttpResponse::class, $this->handler->process($this->stubRequest));
    }

    public function testReturnsHttpResponseWithNotFoundCodeAndMessageIfContentBlockDoesNotExist()
    {
        $contentBlockId = 'foo';

        $this->stubRequest->method('getUrl')
            ->willReturn(HttpUrl::fromString(sprintf('http://example.com/api/content_blocks/%s/', $contentBlockId)));

        $this->stubContentBlockService->method('getContentBlock')
            ->willThrowException(new ContentBlockNotFoundException());

        $response = $this->handler->process($this->stubRequest);

        $this->assertSame(HttpResponse::STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(sprintf('Content block "%s" does not exist.', $contentBlockId), $response->getBody());
    }

    public function testReturnsHttpResponseWithBlockContent()
    {
        $this->prepareValidContentBlock('block_id', 'block_content');

        $response = $this->handler->process($this->stubRequest);

        $this->assertEquals('block_content', $response->getBody());
    }
}
