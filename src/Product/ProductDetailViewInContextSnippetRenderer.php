<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\Snippet;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductDetailViewInContextSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var Product
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
     * @param Product $product
     * @param Context $context
     * @return SnippetList
     */
    public function render(Product $product, Context $context)
    {
        $this->product = $product;
        $this->context = $context;
        $this->snippetList->clear();

        $this->addProductDetailViewSnippetsToSnippetList();

        return $this->snippetList;
    }

    private function addProductDetailViewSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->productDetailViewSnippetKeyGenerator->getKeyForContext(
            $this->context,
            ['product_id' => $this->product->getId()]
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
            ['url_key' => $this->product->getFirstValueOfAttribute('url_key')]
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
