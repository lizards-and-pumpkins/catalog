<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class ProductInListingInContextSnippetRenderer
{
    const CODE = 'product_in_listing';
    
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
     * @var ProductInListingBlockRenderer
     */
    private $blockRenderer;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ProductInListingBlockRenderer $blockRenderer
     * @param SnippetKeyGenerator $snippetKeyGenerator
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ProductInListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
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
        
        $this->addProductInListingSnippetsToSnippetResultList();
        
        return $this->snippetResultList;
    }
    
    private function addProductInListingSnippetsToSnippetResultList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->snippetKeyGenerator->getKeyForContext($this->product->getId(), $this->context);
        $contentSnippet = SnippetResult::create($key, $content);
        $this->snippetResultList->add($contentSnippet);
    }

    /**
     * @return string[]
     */
    public function getUsedContextParts()
    {
        return $this->snippetKeyGenerator->getContextPartsUsedForKey();
    }
}
