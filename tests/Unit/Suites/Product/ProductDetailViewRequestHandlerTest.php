<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\Http\HttpRequestHandler;
use Brera\Page;
use Brera\PageBuilder;

/**
 * @covers Brera\Product\ProductDetailViewRequestHandler
 * @uses   Brera\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $requestHandler;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var string
     */
    private $testUrlPathKey = 'stub-meta-info-key';

    /**
     * @var string
     */
    private $testMetaInfoKey;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var string
     */
    private $testMetaInfoSnippetJson;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageBuilder;

    /**
     * @var string
     */
    private $testProductId = '123';

    protected function setUp()
    {
        $this->testMetaInfoKey = 'product_detail_view_' . $this->testUrlPathKey;
        $this->testMetaInfoSnippetJson = json_encode(ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            'root-snippet-code',
            ['child-snippet1']
        )->getInfo());
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubContext = $this->getMock(Context::class);
        $this->stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);
        $this->requestHandler = new ProductDetailViewRequestHandler(
            $this->testUrlPathKey,
            $this->stubContext,
            $this->mockDataPoolReader,
            $this->stubPageBuilder
        );
    }

    /**
     * @test
     */
    public function itShouldBeARequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess());
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $this->testMetaInfoSnippetJson]
        ]);
        $this->assertTrue($this->requestHandler->canProcess());
    }

    /**
     * @test
     * @expectedException \Brera\Http\UnableToHandleRequestException
     * @expectedExceptionMessage Unable to handle request
     */
    public function itShouldThrowIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->requestHandler->process();
    }

    /**
     * @test
     */
    public function itShouldCreateAPageMetaInfoSnippet()
    {
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $this->testMetaInfoSnippetJson]
        ]);

        $this->requestHandler->process();
        
        $this->assertAttributeInstanceOf(
            ProductDetailPageMetaInfoSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    /**
     * @test
     */
    public function itShouldReturnAPage()
    {
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $this->testMetaInfoSnippetJson]
        ]);
        $this->stubPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            ['product_id' => $this->testProductId]
        )->willReturn($this->getMock(Page::class, [], [], '', false));
        
        $this->assertInstanceOf(Page::class, $this->requestHandler->process());
    }
}
