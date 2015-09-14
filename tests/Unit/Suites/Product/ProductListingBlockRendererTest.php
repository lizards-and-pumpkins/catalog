<?php

namespace Brera\Product;

use Brera\Renderer\AbstractBlockRendererTest;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\BlockStructure;
use Brera\Renderer\ThemeLocator;
use Brera\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \Brera\Product\ProductListingBlockRenderer
 * @uses \Brera\Renderer\BlockRenderer
 */
class ProductListingBlockRendererTest extends AbstractBlockRendererTest
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
        return new ProductListingBlockRenderer($stubThemeLocator, $stubBlockStructure, $stubTranslatorRegistry);
    }
}
