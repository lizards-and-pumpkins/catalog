<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SampleContextSource;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;

/**
 * @covers \Brera\Product\ProductListingSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testSnippetListIsReturned()
    {
        $numItemsPerPage = 10;
        $stubContext = $this->getMock(Context::class);

        /** @var ProductListingSourceList|\PHPUnit_Framework_MockObject_MockObject $stubProductListingSourceList */
        $stubProductListingSourceList = $this->getMock(ProductListingSourceList::class, [], [], '', false);
        $stubProductListingSourceList->expects($this->atLeastOnce())
            ->method('getListOfAvailableNumberOfItemsPerPageForContext')
            ->with($stubContext)
            ->willReturn([$numItemsPerPage]);

        /** @var SnippetList|\PHPUnit_Framework_MockObject_MockObject $mockSnippetList */
        $mockSnippetList = $this->getMock(SnippetList::class);
        $mockSnippetList->expects($this->atLeastOnce())->method('add');

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGenerator */
        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->atLeastOnce())
            ->method('getKeyForContext')
            ->with($stubContext)
            ->willReturn('foo');

        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $mockBlockRenderer */
        $mockBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $mockBlockRenderer->expects($this->atLeastOnce())
            ->method('render')
            ->with($stubProductListingSourceList, $stubContext)
            ->willReturn('bar');

        /** @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject $mockContextSource */
        $mockContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $mockContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $snippetRenderer = new ProductListingSnippetRenderer(
            $mockSnippetList,
            $mockSnippetKeyGenerator,
            $mockBlockRenderer
        );

        $result = $snippetRenderer->render($stubProductListingSourceList, $mockContextSource);

        $this->assertSame($mockSnippetList, $result);
    }
}
