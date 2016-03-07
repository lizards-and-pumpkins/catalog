<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';
    const META_DESCRIPTION_CODE = 'product_detail_view_meta_description';
    const TITLE_KEY_CODE = 'product_view_title';

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $productDetailViewBlockRenderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $productDetailViewSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $productDetailPageMetaSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $productTitleSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $productDetailPageMetaDescriptionSnippetKeyGenerator;

    public function __construct(
        ProductDetailViewBlockRenderer $blockRenderer,
        SnippetKeyGenerator $productDetailViewSnippetKeyGenerator,
        SnippetKeyGenerator $productTitleSnippetKeyGenerator,
        SnippetKeyGenerator $productDetailPageMetaSnippetKeyGenerator,
        SnippetKeyGenerator $productPageMetaDescriptionSnippetKeyGenerator
    ) {
        $this->productDetailViewBlockRenderer = $blockRenderer;
        $this->productDetailViewSnippetKeyGenerator = $productDetailViewSnippetKeyGenerator;
        $this->productTitleSnippetKeyGenerator = $productTitleSnippetKeyGenerator;
        $this->productDetailPageMetaSnippetKeyGenerator = $productDetailPageMetaSnippetKeyGenerator;
        $this->productDetailPageMetaDescriptionSnippetKeyGenerator = $productPageMetaDescriptionSnippetKeyGenerator;
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render(ProductView $productView)
    {
        $contentSnippets = [
            $this->createdContentSnippet($productView),
            $this->createProductTitleSnippet($productView),
            $this->createProductDetailPageMetaDescriptionSnippet($productView),
        ];
        $productMetaSnippets = $this->createProductDetailPageMetaSnippets($productView);

        return array_merge($contentSnippets, $productMetaSnippets);
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    private function createProductDetailPageMetaSnippets(ProductView $productView)
    {
        $pageMetaData = json_encode($this->getPageMetaSnippetContent($productView));
        return array_map(function ($urlKey) use ($pageMetaData, $productView) {
            $key = $this->createPageMetaSnippetKey($urlKey, $productView);
            return Snippet::create($key, $pageMetaData);
        }, $this->getAllProductUrlKeys($productView));
    }

    /**
     * @param ProductView $productView
     * @return Snippet
     */
    private function createdContentSnippet(ProductView $productView)
    {
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );
        $content = $this->productDetailViewBlockRenderer->render($productView, $productView->getContext());

        return Snippet::create($key, $content);
    }

    /**
     * @param ProductView $productView
     * @return Snippet
     */
    private function createProductTitleSnippet(ProductView $productView)
    {
        $key = $this->productTitleSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );
        $content = $productView->getProductPageTitle();

        return Snippet::create($key, $content);
    }

    /**
     * @param ProductView $productView
     * @return Snippet
     */
    private function createProductDetailPageMetaSnippet(ProductView $productView)
    {
        $snippetKey = $this->productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $productView->getFirstValueOfAttribute(Product::URL_KEY)]
        );
        $metaData = $this->getPageMetaSnippetContent($productView);

        return Snippet::create($snippetKey, json_encode($metaData));
    }

    /**
     * @param ProductView $productView
     * @return mixed[]
     */
    private function getPageMetaSnippetContent(ProductView $productView)
    {
        $rootBlockName = $this->productDetailViewBlockRenderer->getRootSnippetCode();
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            (string)$productView->getId(),
            $rootBlockName,
            $this->productDetailViewBlockRenderer->getNestedSnippetCodes(),
            [
                'title'          => [self::TITLE_KEY_CODE],
                'head_container' => [self::META_DESCRIPTION_CODE, ProductCanonicalTagSnippetRenderer::CODE],
            ]
        );

        return $pageMetaInfo->getInfo();
    }

    /**
     * @param ProductView $productView
     * @return Snippet
     */
    private function createProductDetailPageMetaDescriptionSnippet(ProductView $productView)
    {
        $productMetaDescription = $productView->getFirstValueOfAttribute('meta_description');
        $content = sprintf('<meta name="description" content="%s" />', $productMetaDescription);
        $key = $this->productDetailPageMetaDescriptionSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );

        return Snippet::create($key, $content);
    }

    /**
     * @param string $urlKey
     * @param ProductView $productView
     * @return string
     */
    private function createPageMetaSnippetKey($urlKey, ProductView $productView)
    {
        return $this->productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );
    }

    /**
     * @param ProductView $productView
     * @return string[]
     */
    private function getAllProductUrlKeys(ProductView $productView)
    {
        return array_merge(
            [$productView->getFirstValueOfAttribute(Product::URL_KEY)],
            $productView->getAllValuesOfAttribute(Product::NON_CANONICAL_URL_KEY)
        );
    }
}
