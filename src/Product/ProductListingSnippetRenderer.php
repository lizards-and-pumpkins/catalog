<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

class ProductListingSnippetRenderer implements SnippetRenderer
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
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        return [
            $this->createPageMetaSnippet($productListing)
        ];
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet
     */
    private function createPageMetaSnippet(ProductListing $productListing)
    {
        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListing);
        $metaDataSnippetContent = $this->getProductListingPageMetaInfoSnippetContent($productListing);
        return Snippet::create($metaDataSnippetKey, $metaDataSnippetContent);
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(ProductListing $productListing)
    {
        $productListingUrlKey = $productListing->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );

        return $snippetKey;
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getProductListingPageMetaInfoSnippetContent(ProductListing $productListing)
    {
        $metaSnippetContent = ProductListingSnippetContent::create(
            $productListing->getCriteria(),
            ProductListingTemplateSnippetRenderer::CODE,
            $this->getPageSnippetCodes($productListing),
            ['title' => [ProductListingTitleSnippetRenderer::CODE]]
        );

        return json_encode($metaSnippetContent->getInfo());
    }

    /**
     * @param ProductListing $productListing
     * @return string[]
     */
    private function getPageSnippetCodes(ProductListing $productListing)
    {
        $context = $this->getContextFromProductListingData($productListing);
        $this->blockRenderer->render($productListing, $context);
        return $this->blockRenderer->getNestedSnippetCodes();
    }

    /**
     * @param ProductListing $productListing
     * @return Context
     */
    private function getContextFromProductListingData(ProductListing $productListing)
    {
        $contextData = $productListing->getContextData();
        return $this->contextBuilder->createContext($contextData);
    }
}
