<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
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
    const CANONICAL_TAG_KEY = 'listing_canonical_tag';
    const HTML_HEAD_META_KEY = 'html_head_meta';

    /**
     * @var ProductListingBlockRenderer
     */
    private $blockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $metaSnippetKeyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var SnippetKeyGenerator
     */
    private $canonicalTagSnippetKeyGenerator;

    /**
     * @var BaseUrlBuilder
     */
    private $baseUrlBuilder;

    /**
     * @var SnippetKeyGenerator
     */
    private $htmlHeadMetaKeyGenerator;

    public function __construct(
        ProductListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $metaSnippetKeyGenerator,
        ContextBuilder $contextBuilder,
        SnippetKeyGenerator $canonicalTagSnippetKeyGenerator,
        BaseUrlBuilder $baseUrlBuilder,
        SnippetKeyGenerator $htmlHeadMetaKeyGenerator
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->metaSnippetKeyGenerator = $metaSnippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
        $this->canonicalTagSnippetKeyGenerator = $canonicalTagSnippetKeyGenerator;
        $this->baseUrlBuilder = $baseUrlBuilder;
        $this->htmlHeadMetaKeyGenerator = $htmlHeadMetaKeyGenerator;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        return [
            $this->createPageMetaSnippet($productListing),
            $this->createListingCanonicalTagSnippet($productListing),
            $this->createHtmlHeadMetaSnippet($productListing),
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
        $snippetKey = $this->metaSnippetKeyGenerator->getKeyForContext(
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
            [
                'title' => [ProductListingTitleSnippetRenderer::CODE],
                'sidebar_container' => [ProductListingDescriptionSnippetRenderer::CODE],
                'head_container' => [self::CANONICAL_TAG_KEY, self::HTML_HEAD_META_KEY],
            ]
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

    /**
     * @param ProductListing $productListing
     * @return Snippet
     */
    private function createListingCanonicalTagSnippet(ProductListing $productListing)
    {
        $key = $this->getProductListingCanonicalTagSnippetKey($productListing);
        $content = $this->createProductListingCanonicalTag($productListing);
        return Snippet::create($key, $content);
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getProductListingCanonicalTagSnippetKey(ProductListing $productListing)
    {
        $productListingUrlKey = $productListing->getUrlKey();
        return $this->canonicalTagSnippetKeyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function createProductListingCanonicalTag(ProductListing $productListing)
    {
        $baseUrl = $this->baseUrlBuilder->create($this->getContextFromProductListingData($productListing));
        $urlKey = $productListing->getUrlKey();
        return sprintf('<link rel="canonical" href="%s%s" />', $baseUrl, $urlKey);
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet
     */
    private function createHtmlHeadMetaSnippet(ProductListing $productListing)
    {
        $productListingUrlKey = $productListing->getUrlKey();
        $key = $this->htmlHeadMetaKeyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );

        $metaDescription = $this->getMetaDescriptionHtml($productListing);
        $metaKeywords = $this->getMetaKeywordsHtml($productListing);
        return Snippet::create($key, $metaDescription . $metaKeywords);
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getMetaDescriptionHtml(ProductListing $productListing)
    {
        $metaDescription = '';
        if ($productListing->hasAttribute('meta_description')) {
            $metaDescription = $productListing->getAttributeValueByCode('meta_description');
        }
        $metaDescription = sprintf('<meta name="description" content="%s" />', $metaDescription);
        return $metaDescription;
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getMetaKeywordsHtml(ProductListing $productListing)
    {
        $metaDescription = '';
        if ($productListing->hasAttribute('meta_keywords')) {
            $metaDescription = $productListing->getAttributeValueByCode('meta_keywords');
        }
        $metaDescription = sprintf('<meta name="keywords" content="%s" />', $metaDescription);
        return $metaDescription;
    }
}
