<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductSearchResultMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_result';

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

    private function renderMetaInfoSnippetForContext(
        ProductsPerPageForContextList $productsPerPageForContextList,
        Context $context
    ) {
        $this->blockRenderer->render($productsPerPageForContextList, $context);

        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $numItemsPerPageForContext = $productsPerPageForContextList->getListOfAvailableNumberOfProductsPerPageForContext(
            $context
        );

        foreach ($numItemsPerPageForContext as $numItemsPerPage) {
            $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext(
                $context,
                ['products_per_page' => $numItemsPerPage]
            );
            $metaSnippetContent = $this->getMetaSnippetContentJson($rootSnippetCode, $pageSnippetCodes);
            $this->snippetList->add(Snippet::create($metaSnippetKey, $metaSnippetContent));
        }
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductSearchResultMetaSnippetContent|string
     */
    private function getMetaSnippetContentJson($rootSnippetCode, array $pageSnippetCodes)
    {
        $metaSnippetContent = ProductSearchResultMetaSnippetContent::create($rootSnippetCode, $pageSnippetCodes);
        return json_encode($metaSnippetContent->getInfo());
    }
}
