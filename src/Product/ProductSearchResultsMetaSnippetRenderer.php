<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\RootSnippetSourceList;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductSearchResultsMetaSnippetRenderer implements SnippetRenderer
{
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
        $availableContexts = $contextSource->getAllAvailableContexts();
        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        foreach ($availableContexts as $context) {
            $numItemsPerPageForContext = $rootSnippetSourceList->getNumItemsPrePageForContext($context);

            foreach ($numItemsPerPageForContext as $numItemsPerPage) {
                $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext(
                    $context,
                    ['items_per_page' => $numItemsPerPage]
                );
                $metaSnippetContent = $this->getMetaSnippetContentJson($rootSnippetCode, $pageSnippetCodes);
                $this->snippetList->add(Snippet::create($metaSnippetKey, $metaSnippetContent));
            }
        }

        return $this->snippetList;
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductSearchResultsMetaSnippetContent|string
     */
    private function getMetaSnippetContentJson($rootSnippetCode, array $pageSnippetCodes)
    {
        $metaSnippetContent = ProductSearchResultsMetaSnippetContent::create($rootSnippetCode, $pageSnippetCodes);
        return json_encode($metaSnippetContent->getInfo());
    }
}
