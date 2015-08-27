<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\Snippet;
use Brera\SnippetList;

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
        $productsPerPageForContext = $productListingSourceList->getListOfAvailableNumberOfItemsPerPageForContext(
            $context
        );

        foreach ($productsPerPageForContext as $numItemsPerPage) {
            $key = $this->snippetKeyGenerator->getKeyForContext($context, ['products_per_page' => $numItemsPerPage]);
            $contentSnippet = Snippet::create($key, $content);
            $this->snippetList->add($contentSnippet);
        }
    }
}
