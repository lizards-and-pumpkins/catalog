<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetResult;
use Brera\SnippetResultList;
use Brera\UrlPathKeyGenerator;

class ProductDetailViewInContextSnippetRenderer
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
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $blockRenderer;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $productSnippetKeyGenerator;
    
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlKeyGenerator;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ProductDetailViewBlockRenderer $blockRenderer
     * @param ProductDetailSnippetKeyGenerator $snippetKeyGenerator
     * @param UrlPathKeyGenerator $urlKeyGenerator
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ProductDetailViewBlockRenderer $blockRenderer,
        ProductDetailSnippetKeyGenerator $snippetKeyGenerator,
        UrlPathKeyGenerator $urlKeyGenerator
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->blockRenderer = $blockRenderer;
        $this->productSnippetKeyGenerator = $snippetKeyGenerator;
        $this->urlKeyGenerator = $urlKeyGenerator;
    }
    
    /**
     * @param Product $product
     * @param Context $context
     * @return SnippetResultList
     */
    public function render(Product $product, Context $context)
    {
        $this->product = $product;
        $this->context = $context;
        $this->snippetResultList->clear();
        
        $this->addProductDetailViewSnippetsToSnippetResultList();
        
        return $this->snippetResultList;
    }
    
    private function addProductDetailViewSnippetsToSnippetResultList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->productSnippetKeyGenerator->getKeyForContext(
            $this->context,
            ['product_id' => $this->product->getId()]
        );
        $contentSnippet = SnippetResult::create($key, $content);
        $this->snippetResultList->add($contentSnippet);
        
        $pageMetaDataSnippet = $this->getProductDetailPageMetaSnippet();
        $this->snippetResultList->add($pageMetaDataSnippet);
    }

    /**
     * @return SnippetResult
     */
    private function getProductDetailPageMetaSnippet()
    {
        $snippetKey = $this->urlKeyGenerator->getUrlKeyForPathInContext(
            $this->product->getAttributeValue('url_key'),
            $this->context
        );
        $metaData = $this->getPageMetaData();
        return SnippetResult::create($snippetKey, json_encode($metaData));
    }

    /**
     * @return array
     */
    private function getPageMetaData()
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
        return $this->productSnippetKeyGenerator->getContextPartsUsedForKey();
    }
}
