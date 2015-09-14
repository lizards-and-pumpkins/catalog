<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
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
     * @param ProductListingMetaInfoSource $productListingMetaInfoSource
     * @return SnippetList
     */
    public function render(ProductListingMetaInfoSource $productListingMetaInfoSource)
    {
        $contextData = $productListingMetaInfoSource->getContextData();
        $context = $this->contextBuilder->createContext($contextData);

        $this->blockRenderer->render($productListingMetaInfoSource, $context);

        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListingMetaInfoSource, $context);
        $metaDataSnippetContent = $this->getProductListingPageMetaInfoSnippetContent($productListingMetaInfoSource);
        $snippet = Snippet::create($metaDataSnippetKey, $metaDataSnippetContent);

        $this->snippetList->add($snippet);

        return $this->snippetList;
    }

    /**
     * @param ProductListingMetaInfoSource $productListingMetaInfoSource
     * @param Context $context
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(
        ProductListingMetaInfoSource $productListingMetaInfoSource,
        Context $context
    ) {
        $productListingUrlKey = $productListingMetaInfoSource->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, ['url_key' => $productListingUrlKey]);

        return $snippetKey;
    }

    /**
     * @param ProductListingMetaInfoSource $productListingMetaInfoSource
     * @return string
     */
    private function getProductListingPageMetaInfoSnippetContent(
        ProductListingMetaInfoSource $productListingMetaInfoSource
    ) {
        $pageSnippetCodes = $this->blockRenderer->getNestedSnippetCodes();

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $productListingMetaInfoSource->getCriteria(),
            ProductListingSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
