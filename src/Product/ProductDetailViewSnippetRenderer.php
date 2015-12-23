<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var ProductView
     */
    private $productView;

    /**
     * @var SnippetList
     */
    private $snippetList;

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

    public function __construct(
        SnippetList $snippetList,
        ProductDetailViewBlockRenderer $blockRenderer,
        SnippetKeyGenerator $productDetailViewSnippetKeyGenerator,
        SnippetKeyGenerator $productDetailPageMetaSnippetKeyGenerator
    ) {
        $this->snippetList = $snippetList;
        $this->blockRenderer = $blockRenderer;
        $this->productDetailViewSnippetKeyGenerator = $productDetailViewSnippetKeyGenerator;
        $this->productDetailPageMetaSnippetKeyGenerator = $productDetailPageMetaSnippetKeyGenerator;
    }

    /**
     * @param ProductView $product
     * @return SnippetList
     */
    public function render(ProductView $product)
    {
        $this->productView = $product;
        $this->snippetList->clear();

        $this->addProductDetailViewSnippetsToSnippetList();

        return $this->snippetList;
    }

    private function addProductDetailViewSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->productView, $this->productView->getContext());
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $this->productView->getContext(),
            [Product::ID => $this->productView->getId()]
        );
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);

        $pageMetaDataSnippet = $this->getProductDetailPageMetaSnippet();
        $this->snippetList->add($pageMetaDataSnippet);
    }

    /**
     * @return Snippet
     */
    private function getProductDetailPageMetaSnippet()
    {
        $snippetKey = $this->productDetailPageMetaSnippetKeyGenerator->getKeyForContext(
            $this->productView->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $this->productView->getFirstValueOfAttribute(Product::URL_KEY)]
        );
        $metaData = $this->getPageMetaSnippetContent();
        return Snippet::create($snippetKey, json_encode($metaData));
    }

    /**
     * @return mixed[]
     */
    private function getPageMetaSnippetContent()
    {
        $rootBlockName = $this->blockRenderer->getRootSnippetCode();
        $pageMetaInfo = ProductDetailPageMetaInfoSnippetContent::create(
            (string) $this->productView->getId(),
            $rootBlockName,
            $this->blockRenderer->getNestedSnippetCodes()
        );
        return $pageMetaInfo->getInfo();
    }

    /**
     * @return string[]
     */
    public function getUsedContextParts()
    {
        return $this->productDetailViewSnippetKeyGenerator->getContextPartsUsedForKey();
    }
}
