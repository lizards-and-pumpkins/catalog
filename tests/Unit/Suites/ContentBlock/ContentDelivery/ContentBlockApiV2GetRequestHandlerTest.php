<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\ContentBlockNotFoundException;
use LizardsAndPumpkins\ContentBlock\ContentDelivery\Exception\UnableToProcessContentBlockApiGetRequestException;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * Class ContentBlockApiV2GetRequestHandlerTest
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 */
class ContentBlockApiV2GetRequestHandlerTest extends TestCase
{

    /**
     * @var ContentBlockApiV2GetRequestHandler
     */
    protected $handler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ContentBlockService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentBlockService;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextBuilder;

    public function setUp()
    {

        $this->contentBlockService = $this->createMock(ContentBlockService::class);
        $this->contextBuilder = $this->createMock(ContextBuilder::class);
        $this->handler = new ContentBlockApiV2GetRequestHandler($this->contentBlockService, $this->contextBuilder);
        $this->request = $this->createMock(HttpRequest::class);

        parent::setUp();
    }

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->handler);
    }

    public function testCanNotProcessIfQueryParameterIsMissing()
    {
        $this->request->method('hasQueryParameter')->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)->willReturn(false);

        $this->assertFalse($this->handler->canProcess($this->request));
    }

    /**
     * @dataProvider emptyBlockIdProvider
     *
     * @param $emptyBlockId
     */
    public function testCanNotProcessIfContentBlockIdIsEmpty($emptyBlockId)
    {
        $this->request->method('hasQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn(true);
        $this->request->method('getQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn($emptyBlockId);

        $this->assertFalse($this->handler->canProcess($this->request));
    }

    /**
     * @return array[]
     */
    public function emptyBlockIdProvider(): array
    {
        return [[""], [" "], [PHP_EOL]];
    }

    public function testCanNotProcessIfContentBlockDoesNotExist()
    {
        $constantBlockId = 'foo';
        $this->request->method('hasQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn(true);
        $this->request->method('getQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn($constantBlockId);
        $this->contentBlockService->method('getContentBlock')
                                  ->willThrowException(new ContentBlockNotFoundException());
        $this->assertFalse($this->handler->canProcess($this->request));
    }

    public function testCanProcess()
    {
        $this->prepareValidContentBlock('block_content');
        $this->assertTrue($this->handler->canProcess($this->request));
    }

    public function testReturnsHttpResponse()
    {
        $contentBlockValue = '';
        $this->prepareValidContentBlock($contentBlockValue);

        $this->assertInstanceOf(HttpResponse::class, $this->handler->process($this->request));
    }

    public function testReturnsHttpResponseWithBlockContent()
    {
        $contentBlockValue = 'block_content';
        $this->prepareValidContentBlock($contentBlockValue);

        $httpResponse = $this->handler->process($this->request);
        $this->assertEquals($contentBlockValue, $httpResponse->getBody());
    }

    public function testThrowsException()
    {
        $constantBlockId = 'foo';
        $this->request->method('hasQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn(true);
        $this->request->method('getQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn($constantBlockId);
        $this->contentBlockService->method('getContentBlock')
                                  ->willThrowException(new ContentBlockNotFoundException());
        $this->expectException(UnableToProcessContentBlockApiGetRequestException::class);

        $this->handler->process($this->request);
    }

    /**
     * @param $contentBlockValue
     */
    private function prepareValidContentBlock(string $contentBlockValue)
    {
        $contentBlockName = 'block_name';
        $this->request->method('hasQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn(true);
        $this->request->method('getQueryParameter')
                      ->with(ContentBlockApiV2GetRequestHandler::QUERY_PARAMETER_NAME)
                      ->willReturn($contentBlockName);

        $this->contentBlockService->method('getContentBlock')
                                  ->with($contentBlockName)
                                  ->willReturn($contentBlockValue);
    }
}