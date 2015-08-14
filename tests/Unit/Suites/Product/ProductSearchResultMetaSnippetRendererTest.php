<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \Brera\Product\ProductSearchResultMetaSnippetContent
 * @uses   \Brera\Snippet
 */
class ProductSearchResultMetaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummySnippetKey = 'foo';

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'bar';

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductSearchResultMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingSourceList;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('getRootSnippetCode')->willReturn($this->dummyRootSnippetCode);
        $stubBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->renderer = new ProductSearchResultMetaSnippetRenderer(
            $this->mockSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);

        $this->stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);
        $this->stubProductListingSourceList->method('getListOfAvailableNumberOfItemsPerPageForContext')
            ->willReturn([9]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListIsReturned()
    {
        $result = $this->renderer->render($this->stubProductListingSourceList, $this->stubContextSource);
        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList()
    {
        $expectedSnippetContent = [
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE  => $this->dummyRootSnippetCode,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->dummyRootSnippetCode]
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));
        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($this->stubProductListingSourceList, $this->stubContextSource);
    }
}
