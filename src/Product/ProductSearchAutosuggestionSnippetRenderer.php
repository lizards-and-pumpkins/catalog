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
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer,
        ContextSource $contextSource
    ) {
        $this->snippetList = $snippetList;
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
        $this->snippetList->clear();

        // todo: important! use the data version from $dataObject, whatever that is
        foreach ($this->contextSource->getAllAvailableContexts() as $context) {
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
