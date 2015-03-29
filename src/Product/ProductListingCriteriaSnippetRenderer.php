<?php

namespace Brera\Product;

use Brera\Context\ContextBuilder;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\UrlPathKeyGenerator;

class ProductListingCriteriaSnippetRenderer implements SnippetRenderer
{
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(UrlPathKeyGenerator $urlPathKeyGenerator, ContextBuilder $contextBuilder)
    {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return SnippetResult
     */
    public function render(ProductListingSource $productListingSource)
    {
        $metaDataKey = $this->getProductListingMetaDataKey($productListingSource);
        $metaDataContent = $this->getProductListingPageMetaDataContent($productListingSource);

        return SnippetResult::create($metaDataKey, $metaDataContent);
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return string
     */
    private function getProductListingMetaDataKey(ProductListingSource $productListingSource)
    {
        $contextData = $productListingSource->getContextData();
        $context = $this->contextBuilder->getContext($contextData);

        return $this->urlPathKeyGenerator->getUrlKeyForPathInContext($productListingSource->getUrlKey(), $context);
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return string
     */
    private function getProductListingPageMetaDataContent(ProductListingSource $productListingSource)
    {
        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $productListingSource->getCriteria(),
            ProductListingSnippetRenderer::CODE,
            []
        );

        return json_encode($metaSnippetContent->getInfo());
    }
}
