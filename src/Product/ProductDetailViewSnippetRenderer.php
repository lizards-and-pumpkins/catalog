<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    const MAX_PRODUCT_TITLE_LENGTH = 58;

    const PRODUCT_TITLE_SUFFIX = ' | 21run.com';

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $blockRenderer;

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

    public function __construct(
        ProductDetailViewBlockRenderer $blockRenderer,
        SnippetKeyGenerator $productDetailViewSnippetKeyGenerator,
        SnippetKeyGenerator $productTitleSnippetKeyGenerator,
        SnippetKeyGenerator $productDetailPageMetaSnippetKeyGenerator
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->productDetailViewSnippetKeyGenerator = $productDetailViewSnippetKeyGenerator;
        $this->productTitleSnippetKeyGenerator = $productTitleSnippetKeyGenerator;
        $this->productDetailPageMetaSnippetKeyGenerator = $productDetailPageMetaSnippetKeyGenerator;
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render(ProductView $productView)
    {
        return [
            $this->createdContentMetaSnippet($productView),
            $this->createProductTitleSnippet($productView),
            $this->createProductDetailPageMetaSnippet($productView)
        ];
    }

    /**
     * @param ProductView $productView
     * @return Snippet
     */
    private function createdContentMetaSnippet(ProductView $productView)
    {
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $productView->getContext(),
            [Product::ID => $productView->getId()]
        );
        $content = $this->blockRenderer->render($productView, $productView->getContext());

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
        $content = $this->createProductTitleSnippetContent($productView);

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
     * @return \mixed[]
     */
    private function getPageMetaSnippetContent(ProductView $productView)
    {
        $rootBlockName = $this->blockRenderer->getRootSnippetCode();
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            (string) $productView->getId(),
            $rootBlockName,
            $this->blockRenderer->getNestedSnippetCodes(),
            ['title' => ['product_title']]
        );

        return $pageMetaInfo->getInfo();
    }

    /**
     * @param ProductView $productView
     * @return string
     */
    private function createProductTitleSnippetContent(ProductView $productView)
    {
        $title = $productView->getFirstValueOfAttribute('brand') . ' ' . $productView->getFirstValueOfAttribute('name');
        $productGroup = $productView->getFirstValueOfAttribute('product_group');
        $productStyle = $productView->getFirstValueOfAttribute('style');

        if ($productGroup) {
            $title = $this->addProductTitleElement($title, ' | ' . $productGroup);
        }

        if ($productStyle) {
            $title = $this->addProductTitleElement($title, ' | ' . $productStyle);
        }

        return $title . self::PRODUCT_TITLE_SUFFIX;
    }

    /**
     * @param string $title
     * @param string $element
     * @return string
     */
    private function addProductTitleElement($title, $element)
    {
        if (strlen($title) + strlen($element) + strlen(self::PRODUCT_TITLE_SUFFIX) > self::MAX_PRODUCT_TITLE_LENGTH) {
            return $title;
        }

        return $title . $element;
    }
}
