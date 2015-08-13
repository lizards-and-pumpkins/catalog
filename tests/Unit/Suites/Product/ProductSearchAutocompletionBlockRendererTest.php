<?php

namespace Brera\Product;

use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\ProductSearchAutocompletionBlockRenderer
 * @uses \Brera\Renderer\BlockRenderer
 */
class ProductSearchAutocompletionBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator|\PHPUnit_Framework_MockObject_MockObject $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        \PHPUnit_Framework_MockObject_MockObject $stubThemeLocator,
        BlockStructure $stubBlockStructure
    ) {
        return new ProductSearchAutocompletionBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }

    public function testLayoutHandleIsReturned()
    {
        $result = $this->getBlockRenderer()->getLayoutHandle();
        $this->assertEquals('product_search_autocompletion', $result);
    }
}
