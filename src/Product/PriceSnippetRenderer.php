<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class PriceSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var string
     */
    private $priceAttributeCode;

    /**
     * @param SnippetResultList $snippetResultList
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param string $priceAttributeCode
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        SnippetKeyGenerator $snippetKeyGenerator,
        $priceAttributeCode
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->priceAttributeCode = $priceAttributeCode;
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProductSource $productSource, ContextSource $contextSource)
    {
        $availableContexts = $this->getContextList($contextSource);
        foreach ($availableContexts as $context) {
            $productInContext = $productSource->getProductForContext($context);
            $key = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productInContext->getId()]);
            $priceSting = $productInContext->getAttributeValue($this->priceAttributeCode);
            $price = Price::fromString($priceSting);
            $snippetResult = SnippetResult::create($key, $price->getAmount());
            $this->snippetResultList->add($snippetResult);
        }

        return $this->snippetResultList;
    }


    /**
     * @param ContextSource $contextSource
     * @return \Brera\Context\Context[]
     */
    private function getContextList(ContextSource $contextSource)
    {
        $parts = $this->snippetKeyGenerator->getContextPartsUsedForKey();

        return $contextSource->getAllAvailableContexts($parts);
    }
}
