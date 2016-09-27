<?php

namespace LizardsAndPumpkins\ProductSearch\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Import\TemplateRendering\AbstractBlockRendererTest;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\TemplateRendering\ProductInSearchAutosuggestionBlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 */
class ProductInSearchAutosuggestionBlockRendererTest extends AbstractBlockRendererTest
{
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $baseUrlBuilder
    ) : BlockRenderer {
        return new ProductInSearchAutosuggestionBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $baseUrlBuilder
        );
    }
}
