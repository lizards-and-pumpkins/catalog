<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Renderer\AbstractBlockRendererTest;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Renderer\BlockStructure;

/**
 * @covers \LizardsAndPumpkins\PaginationBlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 */
class PaginationBlockRendererTest extends AbstractBlockRendererTest
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
        return new PaginationBlockRenderer($stubThemeLocator, $stubBlockStructure);
    }
}
