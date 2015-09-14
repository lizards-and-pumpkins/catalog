<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \Brera\Product\ProductInListingBlockRenderer
 * @uses   \Brera\Renderer\BlockRenderer
 * @uses   \Brera\Renderer\BlockStructure
 * @uses   \Brera\Renderer\Block
 */
class ProductInListingBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param TranslatorRegistry $stubTranslatorRegistry
     * @return BlockRenderer
     */
    final protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry
    ) {
        return new ProductInListingBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslatorRegistry);
    }
}
