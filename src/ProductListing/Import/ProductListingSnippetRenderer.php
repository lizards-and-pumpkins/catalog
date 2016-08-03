<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;

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
    private $htmlHeadMetaKeyGenerator;

    public function __construct(
        ProductListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $metaSnippetKeyGenerator,
        ContextBuilder $contextBuilder,
        SnippetKeyGenerator $htmlHeadMetaKeyGenerator
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->metaSnippetKeyGenerator = $metaSnippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
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
        return $this->getMetaHtmlFromAttribute($productListing, 'meta_description', 'description');
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function getMetaKeywordsHtml(ProductListing $productListing)
    {
        return $this->getMetaHtmlFromAttribute($productListing, 'meta_keywords', 'keywords');
    }

    /**
     * @param ProductListing $productListing
     * @param string $attribute
     * @param string $metaName
     * @return string
     */
    private function getMetaHtmlFromAttribute(ProductListing $productListing, $attribute, $metaName)
    {
        $attributeValue = '';
        if ($productListing->hasAttribute($attribute)) {
            $attributeValue = $productListing->getAttributeValueByCode($attribute);
        }
        $metaHtml = sprintf('<meta name="%s" content="%s" />', $metaName, htmlspecialchars($attributeValue));
        return $metaHtml;
    }
}
