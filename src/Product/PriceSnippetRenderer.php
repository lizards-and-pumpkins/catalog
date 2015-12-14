<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Product\Tax\TwentyOneRunTaxServiceLocatorOptions;
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
     * @var TaxServiceLocator
     */
    private $taxServiceLocator;

    /**
     * @param TaxableCountries $taxableCountries
     * @param TaxServiceLocator $taxServiceLocator
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param ContextBuilder $contextBuilder
     * @param string $priceAttributeCode
     */
    public function __construct(
        TaxableCountries $taxableCountries,
        TaxServiceLocator $taxServiceLocator,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder,
        $priceAttributeCode
    ) {
        $this->taxableCountries = $taxableCountries;
        $this->taxServiceLocator = $taxServiceLocator;
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
        return @array_map(function ($country) use ($product) {
            return $this->createPriceSnippetForCountry($product, $country);
        }, $this->taxableCountries->getCountries());
    }

    /**
     * @param Product $product
     * @param string $country
     * @return Snippet
     */
    private function createPriceSnippetForCountry(Product $product, $country)
    {
        $key = $this->getSnippetKeyForCountry($product, $country);
        $price = $this->getPriceIncludingTax($product, $country);
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

    /**
     * @param Product $product
     * @param string $country
     * @return Price
     */
    private function getPriceIncludingTax(Product $product, $country)
    {
        $amount = $product->getFirstValueOfAttribute($this->priceAttributeCode);
        $price = new Price($amount);
        // todo: change tax rate instantiation so it does not refer to concrete classes in generic code
        $taxServiceLocatorOptions = TwentyOneRunTaxServiceLocatorOptions::fromStrings(
            $product->getContext()->getValue(ContextWebsite::CODE),
            $product->getTaxClass(),
            $country
        );
        return $this->taxServiceLocator->get($taxServiceLocatorOptions)->applyTo($price);
    }
}
