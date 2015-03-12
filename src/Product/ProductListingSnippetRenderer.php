<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\ProjectionSourceData;
use Brera\Renderer\BlockRenderer;
use Brera\RootSnippetSourceList;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class ProductListingSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;

    /**
     * @param SnippetResultList $snippetResultList
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param BlockRenderer $blockRenderer
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
    }

    /**
     * @param ProjectionSourceData $rootSnippetSourceList
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $rootSnippetSourceList, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $this->renderProductListingSnippetsForContext($rootSnippetSourceList, $context);
        }

        return $this->snippetResultList;
    }

    /**
     * @param RootSnippetSourceList $rootSnippetSourceList
     * @param Context $context
     */
    private function renderProductListingSnippetsForContext(
        RootSnippetSourceList $rootSnippetSourceList,
        Context $context
    ) {
        $content = $this->blockRenderer->render($rootSnippetSourceList, $context);
        $numItemsPerPageForContext = $rootSnippetSourceList->getNumItemsPrePageForContext($context);

        foreach ($numItemsPerPageForContext as $numItemsPerPage) {
            $key = $this->snippetKeyGenerator->getKeyForContext('product_listing', $numItemsPerPage, $context);
            $contentSnippet = SnippetResult::create($key, $content);
            $this->snippetResultList->add($contentSnippet);
        }
    }
}
