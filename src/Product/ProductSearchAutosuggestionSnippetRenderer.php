<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
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

    /**
     * @param mixed $dataObject
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($dataObject, ContextSource $contextSource)
    {
        $this->snippetList->clear();

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $snippet = $this->createSearchAutosuggestionSnippetForContext($dataObject, $context);
            $this->snippetList->add($snippet);
        }

        return $this->snippetList;
    }

    /**
     * @param mixed $dataObject
     * @param Context $context
     * @return Snippet
     */
    private function createSearchAutosuggestionSnippetForContext($dataObject, Context $context)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $content = $this->blockRenderer->render($dataObject, $context);

        return Snippet::create($key, $content);
    }
}
