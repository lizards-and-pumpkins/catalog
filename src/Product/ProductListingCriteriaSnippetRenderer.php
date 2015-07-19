<?php

namespace Brera\Product;

use Brera\Context\ContextBuilder;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\Snippet;
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

    /**
     * @var SnippetList
     */
    private $snippetList;

    public function __construct(
        SnippetList $snippetList,
        UrlPathKeyGenerator $urlPathKeyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetList = $snippetList;
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return SnippetList
     */
    public function render(ProductListingSource $productListingSource)
    {
        $metaDataKey = $this->getProductListingMetaDataKey($productListingSource);
        $metaDataContent = $this->getProductListingPageMetaDataContent($productListingSource);
        $snippet =  Snippet::create($metaDataKey, $metaDataContent);

        $this->snippetList->add($snippet);

        return $this->snippetList;
    }

    /**
     * @param ProductListingSource $productListingSource
     * @return string
     */
    private function getProductListingMetaDataKey(ProductListingSource $productListingSource)
    {
        $contextData = $productListingSource->getContextData();
        $context = $this->contextBuilder->getContext($contextData);

        $key = $this->urlPathKeyGenerator->getUrlKeyForPathInContext($productListingSource->getUrlKey(), $context);
        return ProductListingSnippetRenderer::CODE . '_' . $key;
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
