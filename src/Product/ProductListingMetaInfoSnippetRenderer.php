<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

class ProductListingMetaInfoSnippetRenderer implements SnippetRenderer
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
     * @param ProductListingMetaInfo $productListingMetaInfo
     * @return SnippetList
     */
    public function render(ProductListingMetaInfo $productListingMetaInfo)
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
     * @param ProductListingMetaInfo $productListingMetaInfo
     * @param Context $context
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(
        ProductListingMetaInfo $productListingMetaInfo,
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
     * @param ProductListingMetaInfo $productListingMetaInfo
     * @return string
     */
    private function getProductListingPageMetaInfoSnippetContent(
        ProductListingMetaInfo $productListingMetaInfo
    ) {
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $productListingMetaInfo->getCriteria(),
            ProductListingSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
