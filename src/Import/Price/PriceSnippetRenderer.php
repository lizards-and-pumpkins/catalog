<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Price;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;

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
     * @var AttributeCode
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

    public function __construct(
        TaxableCountries $taxableCountries,
        TaxServiceLocator $taxServiceLocator,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextBuilder $contextBuilder,
        AttributeCode $priceAttributeCode
    ) {
        $this->taxableCountries = $taxableCountries;
        $this->taxServiceLocator = $taxServiceLocator;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextBuilder = $contextBuilder;
        $this->priceAttributeCode = $priceAttributeCode;
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render(ProductView $productView) : array
    {
        $originalProduct = $productView->getOriginalProduct();
        return $this->getPriceSnippets($originalProduct);
    }

    /**
     * @param Product $product
     * @return Snippet[]
     */
    private function getPriceSnippets(Product $product) : array
    {
        if (!$product->hasAttribute($this->priceAttributeCode)) {
            return [];
        }

        return $this->createPriceSnippetForEachCountry($product);
    }

    /**
     * @param Product $product
     * @return Snippet[]
     */
    private function createPriceSnippetForEachCountry(Product $product) : array
    {
        return array_map(function ($country) use ($product) {
            return $this->createPriceSnippetForCountry($product, $country);
        }, $this->taxableCountries->getCountries());
    }

    private function createPriceSnippetForCountry(Product $product, string $country) : Snippet
    {
        $context = $this->getProductContextWithCountry($product, $country);
        $key = $this->getSnippetKeyForCountry($product, $context);
        $price = $this->getPriceIncludingTax($product, $context);
        return Snippet::create($key, (string) $price->getAmount());
    }

    private function getProductContextWithCountry(Product $product, string $country) : Context
    {
        return $this->contextBuilder->expandContext($product->getContext(), [Country::CONTEXT_CODE => $country]);
    }

    private function getSnippetKeyForCountry(Product $product, Context $context) : string
    {
        return $this->snippetKeyGenerator->getKeyForContext($context, [Product::ID => $product->getId()]);
    }

    private function getPriceIncludingTax(Product $product, Context $context) : Price
    {
        $amount = $product->getFirstValueOfAttribute((string) $this->priceAttributeCode);
        $taxServiceLocatorOptions = [
            TaxServiceLocator::OPTION_WEBSITE => $context->getValue(Website::CONTEXT_CODE),
            TaxServiceLocator::OPTION_PRODUCT_TAX_CLASS => $product->getTaxClass(),
            TaxServiceLocator::OPTION_COUNTRY => $context->getValue(Country::CONTEXT_CODE),
        ];
        
        return $this->taxServiceLocator->get($taxServiceLocatorOptions)->applyTo(Price::fromFractions($amount));
    }
}
