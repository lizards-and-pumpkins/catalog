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
        $contextData = $productListing->getContextData();
        $context = $this->contextBuilder->createContext($contextData);

        $this->blockRenderer->render($productListing, $context);

        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListing, $context);
        $metaDataSnippetContent = $this->getProductListingPageMetaInfoSnippetContent($productListing);

        return [
            Snippet::create($metaDataSnippetKey, $metaDataSnippetContent)
        ];
    }

    /**
     * @param ProductListing $productListing
     * @param Context $context
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(ProductListing $productListing, Context $context)
    {
        $productListingUrlKey = $productListing->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $context,
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
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetContent = ProductListingSnippetContent::create(
            $productListing->getCriteria(),
            ProductListingTemplateSnippetRenderer::CODE,
            $pageSnippetCodes,
            ['title' => [ProductListingTitleSnippetRenderer::CODE]]
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
