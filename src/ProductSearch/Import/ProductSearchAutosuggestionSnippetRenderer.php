<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

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
     * @return Snippet[]
     */
    public function render($dataObject)
    {
        // todo: important! use the data version from $dataObject, whatever that is
        return array_map(function (Context $context) use ($dataObject) {
            return $this->createSearchAutosuggestionSnippetForContext($dataObject, $context);
        }, $this->contextSource->getAllAvailableContexts());
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
