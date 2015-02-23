<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class ProductInContextDetailViewSnippetRenderer
{
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
     * @var ProductDetailViewSnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ProductDetailViewBlockRenderer $blockRenderer
     * @param ProductDetailViewSnippetKeyGenerator $keyGenerator
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ProductDetailViewBlockRenderer $blockRenderer,
        ProductDetailViewSnippetKeyGenerator $keyGenerator
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->blockRenderer = $blockRenderer;
        $this->keyGenerator = $keyGenerator;
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
        $key = $this->keyGenerator->getKeyForContext($this->product->getId(), $this->context);
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
        $snippetKey = $this->keyGenerator->getUrlKeyForPathInContext(
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
        $metaData = [
            'source_id' => $this->product->getId(),
            'root_snippet_key' => $rootBlockName,
            'page_snippet_keys' => array_merge([$rootBlockName], $this->blockRenderer->getNestedSnippetCodes())
        ];
        return $metaData;
    }
}
