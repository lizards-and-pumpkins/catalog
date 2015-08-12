<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockRendererTestAbstract;
use Brera\Renderer\BlockStructure;
use Brera\ThemeLocator;

/**
 * @covers \Brera\Product\ProductInSearchAutocompletionBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 */
class ProductInSearchAutocompletionBlockRendererTest extends BlockRendererTestAbstract
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
        return new ProductInSearchAutocompletionBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }

    public function testLayoutHandleIsReturned()
    {
        $result = $this->getBlockRenderer()->getLayoutHandle();
        $this->assertEquals('product_in_autocompletion', $result);
    }
}
