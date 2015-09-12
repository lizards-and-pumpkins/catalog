<?php

namespace Brera;

use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;
use Brera\Translation\Translator;

/**
 * @covers \Brera\PaginationBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 */
class PaginationBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param Translator $stubTranslator
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        Translator $stubTranslator
    ) {
        return new PaginationBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslator);
    }
}
