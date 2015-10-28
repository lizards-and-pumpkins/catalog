<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;

class PriceSnippetRenderer implements SnippetRenderer
{
    const CODE = 'price';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var string
     */
    private $priceAttributeCode;

    /**
     * @param SnippetList $snippetList
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param string $priceAttributeCode
     */
    public function __construct(SnippetList $snippetList, SnippetKeyGenerator $snippetKeyGenerator, $priceAttributeCode)
    {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->priceAttributeCode = $priceAttributeCode;
    }

    /**
     * @param Product $product
     * @return SnippetList
     */
    public function render(Product $product)
    {
        $this->renderProductPriceInContext($product);

        return $this->snippetList;
    }

    private function renderProductPriceInContext(Product $product)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $amount = $product->getFirstValueOfAttribute($this->priceAttributeCode);
        $price = new Price($amount);
        $snippet = Snippet::create($key, $price->getAmount());
        $this->snippetList->add($snippet);
    }
}
