<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \Brera\Product\ProductDetailViewBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\BlockStructure
 * @uses   \Brera\Renderer\Block
 */
class ProductDetailViewBlockRendererTest extends AbstractBlockRendererTest
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
        return new ProductDetailViewBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslatorRegistry);
    }
}
