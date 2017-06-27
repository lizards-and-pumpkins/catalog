<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent;

class ProductSearchResultMetaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_search_result';

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
    public function render($dataObject): array
    {
        return $this->renderMetaInfoSnippetForContexts($dataObject);
    }

    public function renderMetaInfoSnippetForContexts(TemplateProjectionData $dataObject): array
    {
        return array_map(function (Context $context) use ($dataObject) {
            return $this->renderMetaInfoSnippetForContext($dataObject, $context);
        }, $this->contextSource->getAllAvailableContextsWithVersionApplied($dataObject->getDataVersion()));
    }

    private function renderMetaInfoSnippetForContext(TemplateProjectionData $dataObject, Context $context): Snippet
    {
        $this->blockRenderer->render($dataObject, $context);

        $rootSnippetCode = $this->blockRenderer->getRootSnippetCode();
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $metaSnippetContent = json_encode($this->getMetaSnippetContent($rootSnippetCode, $pageSnippetCodes));

        return Snippet::create($metaSnippetKey, $metaSnippetContent);
    }

    /**
     * @param string $rootSnippetCode
     * @param string[] $pageSnippetCodes
     * @return ProductSearchResultMetaSnippetContent
     */
    private function getMetaSnippetContent(
        string $rootSnippetCode,
        array $pageSnippetCodes
    ) : ProductSearchResultMetaSnippetContent {
        return ProductSearchResultMetaSnippetContent::create($rootSnippetCode, $pageSnippetCodes, []);
    }
}
