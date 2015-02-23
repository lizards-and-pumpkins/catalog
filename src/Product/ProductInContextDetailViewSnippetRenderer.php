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

    public function __construct(
        SnippetResultList $snippetResultList,
        ProductDetailViewBlockRenderer $blockRenderer,
        ProductDetailViewSnippetKeyGenerator $keyGenerator
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->blockRenderer = $blockRenderer;
        $this->keyGenerator = $keyGenerator;
        /*
         * ProductSourceDetailViewSnippetRenderer:
         *     SnippetList + ProductInContextDetailViewSnippetRenderer + (ProductSource + ContextSource)
         * ProductInContextDetailViewSnippetRenderer:
         *     SnippetList + BlockRenderer + KeyGenerator + (Product + Context)
         * BlockRenderer:
         *     ThemeLocator + BlockStructure + Layout + (Product + Context)
         * Blocks
         *     Product + Context + BlockRenderer
         */
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
        $this->snippetResultList->merge($this->blockRenderer->render($this->product, $this->context));
        $pageMetaDataSnippet = $this->getProductDetailPageMetaSnippet();
        
        $this->snippetResultList->add($pageMetaDataSnippet);
    }
    
    private function getProductDetailPageMetaSnippet()
    {
        $rootBlockName = $this->blockRenderer->getRootSnippetCode();
        $metaData = [
            'source_id' => $this->product->getId(),
            'root_snippet_key' => $rootBlockName,
            'page_snippet_keys' => array_merge([$rootBlockName], $this->blockRenderer->getNestedSnippetCodes())
        ];
        $snippetKey = $this->keyGenerator->getUrlKeyForPathInContext(
            $this->product->getAttributeValue('url_key'),
            $this->context
        );
        return SnippetResult::create($snippetKey, json_encode($metaData));
    }
}
