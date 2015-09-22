<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\Listing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductListingBlockRenderer;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

class ProductListingPageSnippetRenderer implements SnippetRenderer
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

    public function __construct(SnippetKeyGenerator $snippetKeyGenerator, ProductListingBlockRenderer $blockRenderer)
    {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
    }

    /**
     * @param Context $context
     * @return Snippet
     */
    public function render(Context $context)
    {
        $content = $this->blockRenderer->render(null, $context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);
        
        return Snippet::create($key, $content);
    }
}
