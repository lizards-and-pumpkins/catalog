<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Renderer\AbstractBlockRendererTest;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Renderer\BlockStructure;
use LizardsAndPumpkins\ThemeLocator;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingBlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 */
class ProductListingBlockRendererTest extends AbstractBlockRendererTest
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
        return new ProductListingBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }
}
