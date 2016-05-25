<?php

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Import\Price\Price;
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
        $price = Price::fromFractions($priceInt)->round($currency->getDefaultFractionDigits());
        $productData['attributes']['raw_price'] = $price->getAmount();
        $productData['attributes']['price'] = $this->formatPriceSnippet($price, $currency);
        $productData['attributes']['price_currency'] = $currency->getCurrencyCode();
        $productData['attributes']['price_faction_digits'] = $currency->getDefaultFractionDigits();
        $productData['attributes']['price_base_unit'] = $currency->getSubUnit();

        if (null !== $specialPriceInt) {
            $specialPrice = Price::fromFractions($specialPriceInt)
                ->round($currency->getDefaultFractionDigits());
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
        $localeString = $this->context->getValue(ContextLocale::CODE);
        return (new IntlFormatter($localeString))->format(new Money($price->getAmount(), $currency));
    }

    /**
     * @return string
     */
    private function getCurrencyCode()
    {
        return 'EUR';
    }
}
