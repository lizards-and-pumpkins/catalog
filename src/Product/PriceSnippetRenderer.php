<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\TaxableCountries;

class PriceSnippetRenderer implements SnippetRenderer
{
    const PRICE = 'price';
    const SPECIAL_PRICE = 'special_price';

    /**
     * @var TaxableCountries
     */
    private $taxableCountries;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var string
     */
    private $priceAttributeCode;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @param TaxableCountries $taxableCountries
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param ContextBuilder $contextBuilder
     * @param string $priceAttributeCode
     */
    public function __construct(
        TaxableCountries $taxableCountries,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder,
        $priceAttributeCode
    ) {
        $this->taxableCountries = $taxableCountries;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
        $this->priceAttributeCode = $priceAttributeCode;
    }

    /**
     * @param ProductView $productView
     * @return SnippetList
     */
    public function render(ProductView $productView)
    {
        $originalProduct = $productView->getOriginalProduct();
        return new SnippetList(...$this->getPriceSnippets($originalProduct));
    }

    /**
     * @param Product $product
     * @return Snippet[]
     */
    private function getPriceSnippets(Product $product)
    {
        return $product->hasAttribute($this->priceAttributeCode) ?
            $this->createPriceSnippetForEachCountry($product) :
            [];
    }

    /**
     * @param Product $product
     * @return Snippet[]
     */
    private function createPriceSnippetForEachCountry(Product $product)
    {
        return array_map(function ($country) use ($product) {
            return $this->createPriceSnipperForCountry($product, $country);
        }, $this->taxableCountries->getCountries());
    }

    /**
     * @param Product $product
     * @param string $country
     * @return Snippet
     */
    private function createPriceSnipperForCountry(Product $product, $country)
    {
        $key = $this->getSnippetKeyForCountry($product, $country);
        $amount = $product->getFirstValueOfAttribute($this->priceAttributeCode);
        $price = new Price($amount);
        // todo: apply tax here
        return Snippet::create($key, $price->getAmount());
    }

    /**
     * @param Product $product
     * @param string $country
     * @return string
     */
    private function getSnippetKeyForCountry(Product $product, $country)
    {
        $context = $this->getProductContextWithCountry($product, $country);
        return $this->snippetKeyGenerator->getKeyForContext($context, [Product::ID => $product->getId()]);
    }

    /**
     * @param Product $product
     * @param string $country
     * @return Context
     */
    private function getProductContextWithCountry(Product $product, $country)
    {
        return $this->contextBuilder->expandContext($product->getContext(), [ContextCountry::CODE => $country]);
    }
}
