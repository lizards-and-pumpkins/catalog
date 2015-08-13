<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\RootSnippetSourceList;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;


class ProductSearchAutosuggestionSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_autosuggestion';

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

    public function render(RootSnippetSourceList $rootSnippetSourceList, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $snippet = $this->createSearchAutosuggestionSnippetsForContext($rootSnippetSourceList, $context);
            $this->snippetList->add($snippet);
        }

        return $this->snippetList;
    }

    /**
     * @param RootSnippetSourceList $rootSnippetSourceList
     * @param Context $context
     * @return Snippet
     */
    private function createSearchAutosuggestionSnippetsForContext(
        RootSnippetSourceList $rootSnippetSourceList,
        Context $context
    ) {
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $content = $this->blockRenderer->render($rootSnippetSourceList, $context);

        return Snippet::create($key, $content);
    }
}
