<?php

namespace LizardsAndPumpkins\Import\Price;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\Product\ProductDTO;
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
     * @return Snippet[]
     */
    public function render(ProductView $productView)
    {
        $originalProduct = $productView->getOriginalProduct();
        return $this->getPriceSnippets($originalProduct);
    }

    /**
     * @param ProductDTO $product
     * @return Snippet[]
     */
    private function getPriceSnippets(ProductDTO $product)
    {
        if (!$product->hasAttribute($this->priceAttributeCode)) {
            return [];
        }

        return $this->createPriceSnippetForEachCountry($product);
    }

    /**
     * @param ProductDTO $product
     * @return Snippet[]
     */
    private function createPriceSnippetForEachCountry(ProductDTO $product)
    {
        return @array_map(function ($country) use ($product) {
            return $this->createPriceSnippetForCountry($product, $country);
        }, $this->taxableCountries->getCountries());
    }

    /**
     * @param ProductDTO $product
     * @param string $country
     * @return Snippet
     */
    private function createPriceSnippetForCountry(ProductDTO $product, $country)
    {
        $context = $this->getProductContextWithCountry($product, $country);
        $key = $this->getSnippetKeyForCountry($product, $context);
        $price = $this->getPriceIncludingTax($product, $context);
        return Snippet::create($key, $price->getAmount());
    }

    /**
     * @param ProductDTO $product
     * @param string $country
     * @return Context
     */
    private function getProductContextWithCountry(ProductDTO $product, $country)
    {
        return $this->contextBuilder->expandContext($product->getContext(), [Country::CONTEXT_CODE => $country]);
    }

    /**
     * @param ProductDTO $product
     * @param Context $context
     * @return string
     */
    private function getSnippetKeyForCountry(ProductDTO $product, Context $context)
    {
        return $this->snippetKeyGenerator->getKeyForContext($context, [ProductDTO::ID => $product->getId()]);
    }

    /**
     * @param ProductDTO $product
     * @param Context $context
     * @return Price
     */
    private function getPriceIncludingTax(ProductDTO $product, Context $context)
    {
        $amount = $product->getFirstValueOfAttribute($this->priceAttributeCode);
        $taxServiceLocatorOptions = [
            TaxServiceLocator::OPTION_WEBSITE => $context->getValue(Website::CONTEXT_CODE),
            TaxServiceLocator::OPTION_PRODUCT_TAX_CLASS => $product->getTaxClass(),
            TaxServiceLocator::OPTION_COUNTRY => $context->getValue(Country::CONTEXT_CODE),
        ];
        
        return $this->taxServiceLocator->get($taxServiceLocatorOptions)->applyTo(Price::fromFractions($amount));
    }
}
