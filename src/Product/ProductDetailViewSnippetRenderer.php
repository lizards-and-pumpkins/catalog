<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var Product
     */
    private $product;

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
     * @param Product $product
     * @return SnippetList
     */
    public function render(Product $product)
    {
        $this->product = $product;
        $this->snippetList->clear();

        $this->addProductDetailViewSnippetsToSnippetList();

        return $this->snippetList;
    }

    private function addProductDetailViewSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->product, $this->product->getContext());
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $this->product->getContext(),
            [Product::ID => $this->product->getId()]
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
            $this->product->getContext(),
            [PageMetaInfoSnippetContent::URL_KEY => $this->product->getFirstValueOfAttribute(Product::URL_KEY)]
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
            (string) $this->product->getId(),
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
