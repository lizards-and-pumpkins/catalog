<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Import\TemplateRendering\AbstractBlockRendererTest;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockStructure;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateFactory;
use LizardsAndPumpkins\Import\TemplateRendering\ThemeLocator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\Block
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockStructure
 */
class ProductDetailViewBlockRendererTest extends AbstractBlockRendererTest
{
    protected function createRendererInstance(
        ThemeLocator $stubThemeLocator,
        BlockStructure $stubBlockStructure,
        TranslatorRegistry $stubTranslatorRegistry,
        BaseUrlBuilder $stubBaseUrlBuilder,
        BaseUrlBuilder $stubAssetsBaseUrlBuilder,
        TemplateFactory $templateFactory
    ) : BlockRenderer {
        return new ProductDetailViewBlockRenderer(
            $stubThemeLocator,
            $stubBlockStructure,
            $stubTranslatorRegistry,
            $stubBaseUrlBuilder,
            $stubAssetsBaseUrlBuilder,
            $templateFactory
        );
    }
}
