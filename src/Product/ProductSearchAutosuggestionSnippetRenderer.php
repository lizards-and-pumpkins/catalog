<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductSearchAutosuggestionSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_autosuggestion';

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer,
        ContextSource $contextSource
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
        $this->contextSource = $contextSource;
    }

    /**
     * @param mixed $dataObject
     * @return SnippetList
     */
    public function render($dataObject)
    {
        // todo: important! use the data version from $dataObject, whatever that is
        $snippets = array_map(function(Context $context) use ($dataObject) {
            return $this->createSearchAutosuggestionSnippetForContext($dataObject, $context);
        }, $this->contextSource->getAllAvailableContexts());

        return new SnippetList(...$snippets);
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
