<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\RootSnippetSourceList;
use Brera\SampleContextSource;
use Brera\ProjectionSourceData;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;

/**
 * @covers \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnSnippetList()
    {
        $numItemsPerPage = 10;
        $stubContext = $this->getMock(Context::class);

        $mockRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $mockRootSnippetSourceList->expects($this->atLeastOnce())
            ->method('getNumItemsPrePageForContext')
            ->with($stubContext)
            ->willReturn([$numItemsPerPage]);

        $mockSnippetList = $this->getMock(SnippetList::class);
        $mockSnippetList->expects($this->atLeastOnce())
            ->method('add');

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->atLeastOnce())
            ->method('getKeyForContext')
            ->with($stubContext)
            ->willReturn('foo');

        $mockBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $mockBlockRenderer->expects($this->atLeastOnce())
            ->method('render')
            ->with($mockRootSnippetSourceList, $stubContext)
            ->willReturn('bar');

        $mockContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $mockContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $snippetRenderer = new ProductListingSnippetRenderer(
            $mockSnippetList,
            $mockSnippetKeyGenerator,
            $mockBlockRenderer
        );

        $result = $snippetRenderer->render($mockRootSnippetSourceList, $mockContextSource);

        $this->assertSame($mockSnippetList, $result);
    }
}
