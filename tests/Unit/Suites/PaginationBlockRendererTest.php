<?php

namespace Brera;

use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \Brera\PaginationBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 */
class PaginationBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param TranslatorRegistry $stubTranslatorRegistry
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry
    ) {
        return new PaginationBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslatorRegistry);
    }
}
