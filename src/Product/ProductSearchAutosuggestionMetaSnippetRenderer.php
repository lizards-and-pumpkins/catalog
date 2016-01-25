<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductSearchAutosuggestionMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_autosuggestion_meta';

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
        // todo: important! Use the data version embedded in $dataObject, whatever that may be!!
        return array_map(function (Context $context) use ($dataObject) {
            return $this->renderMetaInfoSnippetForContext($dataObject, $context);
        }, $this->contextSource->getAllAvailableContexts());
    }

    /**
     * @param mixed $dataObject
     * @param Context $context
     * @return Snippet
     */
    private function renderMetaInfoSnippetForContext($dataObject, Context $context)
    {
        $this->blockRenderer->render($dataObject, $context);

        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $metaSnippetContent = $this->getMetaSnippetContentJson($rootSnippetCode, $pageSnippetCodes);

        return Snippet::create($metaSnippetKey, $metaSnippetContent);
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductSearchAutosuggestionMetaSnippetContent|string
     */
    private function getMetaSnippetContentJson($rootSnippetCode, array $pageSnippetCodes)
    {
        $metaSnippetContent = ProductSearchAutosuggestionMetaSnippetContent::create(
            $rootSnippetCode,
            $pageSnippetCodes,
            []
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
