<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Product\Price;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\IntlFormatter;
use SebastianBergmann\Money\Money;

class EnrichProductJsonWithPrices
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
    
    /**
     * @param string[] $productData
     * @param int $priceInt
     * @param int|null $specialPriceInt
     * @return array[]
     */
    public function addPricesToProductData(array $productData, $priceInt, $specialPriceInt = null)
    {
        $currency = new Currency($this->getCurrencyCode());
        $price = Price::fromFractions($priceInt)->roundToFractions($currency->getDefaultFractionDigits());
        $productData['attributes']['raw_price'] = $price->getAmount();
        $productData['attributes']['price'] = $this->formatPriceSnippet($price, $currency);
        $productData['attributes']['price_currency'] = $currency->getCurrencyCode();
        $productData['attributes']['price_faction_digits'] = $currency->getDefaultFractionDigits();
        $productData['attributes']['price_base_unit'] = $currency->getSubUnit();

        if (null !== $specialPriceInt) {
            $specialPrice = Price::fromFractions($specialPriceInt)
                ->roundToFractions($currency->getDefaultFractionDigits());
            $productData['attributes']['raw_special_price'] = $specialPrice->getAmount();
            $productData['attributes']['special_price'] = $this->formatPriceSnippet($specialPrice, $currency);
        }

        return $productData;
    }

    /**
     * @param Price $price
     * @param Currency $currency
     * @return string
     */
    private function formatPriceSnippet(Price $price, Currency $currency)
    {
        $locale = $this->getLocaleString($this->context);
        return (new IntlFormatter($locale))->format(new Money($price->getAmount(), $currency));
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getLocaleString(Context $context)
    {
        $locale = $context->getValue(ContextLocale::CODE);
        if (is_null($locale)) {
            throw new NoValidLocaleInContextException('No locale found in context');
        }
        return $locale;
    }

    /**
     * @return string
     */
    private function getCurrencyCode()
    {
        return 'EUR';
    }
}
