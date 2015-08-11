<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\Snippet;
use Brera\SnippetList;

class PriceSnippetRenderer implements SnippetRenderer
{
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
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProductSource $productSource, ContextSource $contextSource)
    {
        $availableContexts = $contextSource->getAllAvailableContexts();
        foreach ($availableContexts as $context) {
            $this->renderProductPriceInContext($productSource, $context);
        }

        return $this->snippetList;
    }

    private function renderProductPriceInContext(ProductSource $productSource, Context $context)
    {
        $productInContext = $productSource->getProductForContext($context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productInContext->getId()]);
        $priceString = $productInContext->getFirstAttributeValue($this->priceAttributeCode);
        $price = Price::fromString($priceString);
        $snippet = Snippet::create($key, $price->getAmount());
        $this->snippetList->add($snippet);
    }
}
