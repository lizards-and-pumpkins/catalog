<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
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

    /**
     * @var SnippetList
     */
    private $snippetList;

    public function __construct(
        SnippetList $snippetList,
        ProductListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetList = $snippetList;
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListingCriteria $productListingMetaInfo
     * @return SnippetList
     */
    public function render(ProductListingCriteria $productListingMetaInfo)
    {
        $contextData = $productListingMetaInfo->getContextData();
        $context = $this->contextBuilder->createContext($contextData);

        $this->blockRenderer->render($productListingMetaInfo, $context);

        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListingMetaInfo, $context);
        $metaDataSnippetContent = $this->getProductListingPageMetaInfoSnippetContent($productListingMetaInfo);
        $snippet = Snippet::create($metaDataSnippetKey, $metaDataSnippetContent);

        $this->snippetList->add($snippet);

        return $this->snippetList;
    }

    /**
     * @param ProductListingCriteria $productListingMetaInfo
     * @param Context $context
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(
        ProductListingCriteria $productListingMetaInfo,
        Context $context
    ) {
        $productListingUrlKey = $productListingMetaInfo->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext(
            $context,
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );

        return $snippetKey;
    }

    /**
     * @param ProductListingCriteria $productListingMetaInfo
     * @return string
     */
    private function getProductListingPageMetaInfoSnippetContent(
        ProductListingCriteria $productListingMetaInfo
    ) {
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetContent = ProductListingCriteriaSnippetContent::create(
            $productListingMetaInfo->getCriteria(),
            ProductListingPageSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
