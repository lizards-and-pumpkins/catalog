<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductSearchAutosuggestionMetaSnippetRenderer implements SnippetRenderer
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

    public function render(ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $this->renderMetaInfoSnippetForContext($context);
        }

        return $this->snippetList;
    }

    private function renderMetaInfoSnippetForContext(Context $context)
    {
        $dummyDataObject = []; // TODO: Do it clean!
        $this->blockRenderer->render($dummyDataObject, $context);

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
