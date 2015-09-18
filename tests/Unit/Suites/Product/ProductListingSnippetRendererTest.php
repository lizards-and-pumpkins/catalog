<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\SampleContextSource;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testSnippetListIsReturned()
    {
        $numItemsPerPage = 10;
        $stubContext = $this->getMock(Context::class);
        
        $stubProductsPerPageForContextList = $this->getMock(ProductsPerPageForContextList::class, [], [], '', false);
        $stubProductsPerPageForContextList->expects($this->atLeastOnce())
            ->method('getListOfAvailableNumberOfProductsPerPageForContext')
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
            ->with($stubProductsPerPageForContextList, $stubContext)
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

        $result = $snippetRenderer->render($stubProductsPerPageForContextList, $mockContextSource);

        $this->assertSame($mockSnippetList, $result);
    }
}
