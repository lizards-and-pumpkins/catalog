<?php

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;

use LizardsAndPumpkins\Import\TemplateRendering\AbstractBlockRendererTest;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 */
class ProductListingBlockRendererTest extends AbstractBlockRendererTest
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
        return new ProductListingBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $baseUrlBuilder
        );
    }
}
