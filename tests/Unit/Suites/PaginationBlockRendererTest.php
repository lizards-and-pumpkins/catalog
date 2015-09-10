<?php

namespace Brera;

use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;

/**
 * @covers \Brera\PaginationBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
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
