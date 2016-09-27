<?php

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Import\TemplateRendering\AbstractBlockRendererTest;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlockRenderer
 * @uses \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 */
class ProductListingDescriptionBlockRendererTest extends AbstractBlockRendererTest
{
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    ) : BlockRenderer {
        return new ProductListingDescriptionBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $baseUrlBuilder
        );
    }
}
