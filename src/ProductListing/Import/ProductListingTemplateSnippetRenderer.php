<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

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
     * @param mixed $dataObject
     * @return Snippet[]
     */
    public function render($dataObject) : array
    {
        // todo: important! Use data version from $dataObject
        return array_map(function (Context $context) use ($dataObject) {
            return $this->renderProductListingPageSnippetForContext($dataObject, $context);
        }, $this->contextSource->getAllAvailableContexts());

    }

    /**
     * @param mixed $dataObject
     * @param Context $context
     * @return Snippet
     */
    private function renderProductListingPageSnippetForContext($dataObject, Context $context) : Snippet
    {
        $content = $this->blockRenderer->render($dataObject, $context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);

        return Snippet::create($key, $content);
    }
}
