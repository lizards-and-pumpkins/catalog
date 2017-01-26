<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

class ProductListingTemplateSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing';

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var ProductListingBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        ProductListingBlockRenderer $blockRenderer,
        ContextSource $contextSource
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
        $this->contextSource = $contextSource;
    }

    /**
     * @param TemplateProjectionData $dataToRender
     * @return Snippet[]
     */
    public function render($dataToRender): array
    {
        return $this->projectDataForAllContexts($dataToRender);
    }

    private function projectDataForAllContexts(TemplateProjectionData $dataToRender): array
    {
        return array_map(function (Context $context) use ($dataToRender) {
            return $this->renderProductListingPageSnippetForContext($dataToRender, $context);
        }, $this->contextSource->getAllAvailableContextsWithVersionApplied($dataToRender->getDataVersion()));
    }

    private function renderProductListingPageSnippetForContext(
        TemplateProjectionData $dataToRender,
        Context $context
    ): Snippet {
        $content = $this->blockRenderer->render($dataToRender, $context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);

        return Snippet::create($key, $content);
    }
}
