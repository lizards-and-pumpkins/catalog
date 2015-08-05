<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\RootSnippetSourceList;
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
     * @param RootSnippetSourceList $rootSnippetSourceList
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(RootSnippetSourceList $rootSnippetSourceList, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $this->renderProductListingSnippetsForContext($rootSnippetSourceList, $context);
        }

        return $this->snippetList;
    }

    private function renderProductListingSnippetsForContext(
        RootSnippetSourceList $rootSnippetSourceList,
        Context $context
    ) {
        $content = $this->blockRenderer->render($rootSnippetSourceList, $context);
        $numItemsPerPageForContext = $rootSnippetSourceList->getListOfAvailableNumberOfItemsPerPageForContext($context);

        foreach ($numItemsPerPageForContext as $numItemsPerPage) {
            $key = $this->snippetKeyGenerator->getKeyForContext($context, ['products_per_page' => $numItemsPerPage]);
            $contentSnippet = Snippet::create($key, $content);
            $this->snippetList->add($contentSnippet);
        }
    }
}
