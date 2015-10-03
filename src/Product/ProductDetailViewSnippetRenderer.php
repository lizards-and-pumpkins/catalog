<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductDetailViewSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var SimpleProduct
     */
    private $product;

    /**
     * @var Context
     */
    private $context;

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
     * @param SimpleProduct $product
     * @return SnippetList
     */
    public function render(SimpleProduct $product)
    {
        $this->product = $product;
        $this->context = $product->getContext();
        $this->snippetList->clear();

        $this->addProductDetailViewSnippetsToSnippetList();

        return $this->snippetList;
    }

    private function addProductDetailViewSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $this->context,
            [SimpleProduct::ID => $this->product->getId()]
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
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $this->product->getFirstValueOfAttribute(SimpleProduct::URL_KEY)]
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
