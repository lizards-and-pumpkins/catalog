<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;

class ProductListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;

    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
    }

    /**
     * @param ProductListingSourceList $productListingSourceList
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProductListingSourceList $productListingSourceList, ContextSource $contextSource)
    {
        $this->snippetList->clear();

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $this->renderProductListingSnippetsForContext($productListingSourceList, $context);
        }

        return $this->snippetList;
    }

    private function renderProductListingSnippetsForContext(
        ProductListingSourceList $productListingSourceList,
        Context $context
    ) {
        $content = $this->blockRenderer->render($productListingSourceList, $context);
        $productsPerPageForContext = $productListingSourceList->getListOfAvailableNumberOfProductsPerPageForContext(
            $context
        );

        foreach ($productsPerPageForContext as $productsPerPage) {
            $key = $this->snippetKeyGenerator->getKeyForContext($context, ['products_per_page' => $productsPerPage]);
            $contentSnippet = Snippet::create($key, $content);
            $this->snippetList->add($contentSnippet);
        }
    }
}
