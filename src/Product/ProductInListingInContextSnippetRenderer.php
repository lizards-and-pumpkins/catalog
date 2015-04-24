<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\Snippet;
use Brera\SnippetList;

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
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ProductInListingBlockRenderer
     */
    private $blockRenderer;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(
        SnippetList $snippetList,
        ProductInListingBlockRenderer $blockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        $this->snippetList = $snippetList;
        $this->blockRenderer = $blockRenderer;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
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
        
        $this->addProductInListingSnippetsToSnippetList();
        
        return $this->snippetList;
    }
    
    private function addProductInListingSnippetsToSnippetList()
    {
        $content = $this->blockRenderer->render($this->product, $this->context);
        $key = $this->snippetKeyGenerator->getKeyForContext($this->context, ['product_id' => $this->product->getId()]);
        $contentSnippet = Snippet::create($key, $content);
        $this->snippetList->add($contentSnippet);
    }

    /**
     * @return string[]
     */
    public function getUsedContextParts()
    {
        return $this->snippetKeyGenerator->getContextPartsUsedForKey();
    }
}
