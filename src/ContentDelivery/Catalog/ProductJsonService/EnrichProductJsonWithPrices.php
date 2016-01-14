<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
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
     * @param string $price
     * @param string $specialPrice
     * @return array[]
     */
    public function addPricesToProductData(array $productData, $price, $specialPrice)
    {
        $currency = new Currency($this->getCurrencyCode());
        $productData['attributes']['raw_price'] = $price;
        $productData['attributes']['price'] = $this->formatPriceSnippet($price, $currency);
        $productData['attributes']['price_currency'] = $currency->getCurrencyCode();
        $productData['attributes']['price_faction_digits'] = $currency->getDefaultFractionDigits();
        $productData['attributes']['price_base_unit'] = $currency->getSubUnit();

        if (null !== $specialPrice) {
            $productData['attributes']['raw_special_price'] = $specialPrice;
            $productData['attributes']['special_price'] = $this->formatPriceSnippet($specialPrice, $currency);
        }

        return $productData;
    }

    /**
     * @param string $price
     * @param string $currency
     * @return string
     */
    private function formatPriceSnippet($price, Currency $currency)
    {
        $locale = $this->getLocaleString($this->context);
        return (new IntlFormatter($locale))->format(new Money((int) $price, $currency));
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
