<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\ProjectionSourceData;
use Brera\SnippetKeyGenerator;
use Brera\SnippetResultList;

/**
 * @covers \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\SnippetResult
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnSnippetResultList()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContext = $this->getMock(Context::class);

        $mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $mockSnippetResultList->expects($this->atLeastOnce())
            ->method('add');

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->atLeastOnce())
            ->method('getKeyForContext')
            ->with('product_listing', $stubContext)
            ->willReturn('foo');

        $mockBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $mockBlockRenderer->expects($this->atLeastOnce())
            ->method('render')
            ->with($stubProjectionSourceData, $stubContext)
            ->willReturn('bar');

        $mockContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $mockContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $snippetRenderer = new ProductListingSnippetRenderer(
            $mockSnippetResultList, $mockSnippetKeyGenerator, $mockBlockRenderer
        );

        $result = $snippetRenderer->render($stubProjectionSourceData, $mockContextSource);

        $this->assertSame($mockSnippetResultList, $result);
    }
}
