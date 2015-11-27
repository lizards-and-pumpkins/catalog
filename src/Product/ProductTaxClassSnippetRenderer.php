<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class ProductTaxClassSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_tax_class';
    
    /**
     * @var SnippetList
     */
    private $snippetList;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    public function __construct(SnippetList $snippetList, SnippetKeyGenerator $keyGenerator)
    {
        $this->snippetList = $snippetList;
        $this->keyGenerator = $keyGenerator;
    }

    /**
     * @param Product $product
     * @return SnippetList
     */
    public function render(Product $product)
    {
        $key = $this->keyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $snippet = Snippet::create($key, $product->getTaxClass());
        $this->snippetList->add($snippet);
        return $this->snippetList;
    }
}
