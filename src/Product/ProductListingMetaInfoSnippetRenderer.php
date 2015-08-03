<?php

namespace Brera\Product;

use Brera\Context\ContextBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\Snippet;

class ProductListingMetaInfoSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_meta';

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
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return SnippetList
     */
    public function render(ProductListingSource $productListingSource)
    {
        $metaDataSnippetKey = $this->getProductListingMetaDataSnippetKey($productListingSource);
        $metaDataSnippetContent = $this->getProductListingPageMetaDataSnippetContent($productListingSource);
        $snippet = Snippet::create($metaDataSnippetKey, $metaDataSnippetContent);

        $this->snippetList->add($snippet);

        return $this->snippetList;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return string
     */
    private function getProductListingMetaDataSnippetKey(ProductListingSource $productListingSource)
    {
        $contextData = $productListingSource->getContextData();
        $context = $this->contextBuilder->getContext($contextData);

        $productListingUrlKey = $productListingSource->getUrlKey();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, ['url_key' => $productListingUrlKey]);

        return $snippetKey;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return string
     */
    private function getProductListingPageMetaDataSnippetContent(ProductListingSource $productListingSource)
    {
        $pageSnippetCodes = [];

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $productListingSource->getCriteria(),
            ProductListingSnippetRenderer::CODE,
            $pageSnippetCodes
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
