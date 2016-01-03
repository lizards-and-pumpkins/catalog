<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\Listing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductListingBlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductListingPageSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayOfSnippetsIsReturned()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGenerator */
        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->atLeastOnce())->method('getKeyForContext')->with($stubContext)
            ->willReturn('foo');

        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $mockBlockRenderer */
        $mockBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        
        $snippetRenderer = new ProductListingPageSnippetRenderer(
            $mockSnippetKeyGenerator,
            $mockBlockRenderer
        );

        $result = $snippetRenderer->render($stubContext);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
