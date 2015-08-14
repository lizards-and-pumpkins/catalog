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
 * @covers \Brera\Product\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductSearchAutosuggestionSnipperRenderetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductSearchAutosuggestionSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource
     */
    private $stubContextSource;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->snippetRenderer = new ProductSearchAutosuggestionSnippetRenderer(
            $this->mockSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testSnippetListIsReturned()
    {
        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);

        $result = $this->snippetRenderer->render($stubProductListingSourceList, $this->stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetIsAddedToSnippetList()
    {
        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);

        $this->mockSnippetList->expects($this->once())->method('add')->with($this->isInstanceOf(Snippet::class));

        $this->snippetRenderer->render($stubProductListingSourceList, $this->stubContextSource);
    }
}
