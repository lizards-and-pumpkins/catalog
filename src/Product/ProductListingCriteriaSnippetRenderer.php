<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

class ProductListingCriteriaSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_meta';

    /**
     * @var ProductListingBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        ProductListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return Snippet[]
     */
    public function render(ProductListingCriteria $productListingCriteria)
    {
        $contextData = $productListingCriteria->getContextData();
        $context = $this->contextBuilder->createContext($contextData);

        $this->blockRenderer->render($productListingCriteria, $context);

        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListingCriteria, $context);
        $metaDataSnippetContent = $this->getProductListingPageMetaInfoSnippetContent($productListingCriteria);

        return [
            Snippet::create($metaDataSnippetKey, $metaDataSnippetContent)
        ];
    }

    /**
     * @param ProductListingCriteria $productListingCriteria
     * @param Context $context
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(
        ProductListingCriteria $productListingCriteria,
        Context $context
    ) {
        $productListingUrlKey = $productListingCriteria->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );

        return $snippetKey;
    }

    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return string
     */
    private function getProductListingPageMetaInfoSnippetContent(
        ProductListingCriteria $productListingCriteria
    ) {
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetContent = ProductListingCriteriaSnippetContent::create(
            $productListingCriteria->getCriteria(),
            ProductListingTemplateSnippetRenderer::CODE,
            $pageSnippetCodes,
            ['title' => [ProductListingTitleSnippetRenderer::CODE]]
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
