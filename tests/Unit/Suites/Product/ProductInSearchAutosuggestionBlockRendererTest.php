<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Renderer\AbstractBlockRendererTest;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Renderer\BlockStructure;
use LizardsAndPumpkins\Renderer\ThemeLocator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\Product\ProductInSearchAutosuggestionBlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 */
class ProductInSearchAutosuggestionBlockRendererTest extends AbstractBlockRendererTest
{
    /**
     * @param ThemeLocator $stubThemeLocator
     * @param BlockStructure $stubBlockStructure
     * @param TranslatorRegistry $stubTranslatorRegistry
     * @param BaseUrlBuilder $baseUrlBuilder
     * @return BlockRenderer
     */
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    ) {
        return new ProductInSearchAutosuggestionBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $baseUrlBuilder
        );
    }
}
