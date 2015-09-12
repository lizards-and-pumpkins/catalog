<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockStructure;
use Brera\ThemeLocator;
use Brera\Translation\Translator;

/**
 * @covers \Brera\Product\ProductInSearchAutosuggestionBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 */
class ProductInSearchAutosuggestionBlockRendererTest extends AbstractBlockRendererTest
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
        return new ProductInSearchAutosuggestionBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslator);
    }
}
