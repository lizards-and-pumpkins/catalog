<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductSearchAutosuggestionMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_autosuggestion_meta';

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
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $this->renderMetaInfoSnippetForContext($dataObject, $context);
        }

        return $this->snippetList;
    }

    /**
     * @param mixed $dataObject
     * @param Context $context
     */
    private function renderMetaInfoSnippetForContext($dataObject, Context $context)
    {
        $this->blockRenderer->render($dataObject, $context);

        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $metaSnippetContent = $this->getMetaSnippetContentJson($rootSnippetCode, $pageSnippetCodes);

        $this->snippetList->add(Snippet::create($metaSnippetKey, $metaSnippetContent));
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
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
