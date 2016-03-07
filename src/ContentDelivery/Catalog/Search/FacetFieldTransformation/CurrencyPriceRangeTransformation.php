<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\Product\Price;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\IntlFormatter;
use SebastianBergmann\Money\Money;

class CurrencyPriceRangeTransformation implements FacetFieldTransformation
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @param Currency $currency
     * @param string $locale
     */
    public function __construct(Currency $currency, $locale)
    {
        $this->locale = $locale;
        $this->currency = $currency;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(FacetFilterRange $range)
    {
        return sprintf('%s - %s', $this->priceIntToString($range->from()), $this->priceIntToString($range->to()));
    }

    /**
     * @param int $price
     * @return string
     */
    private function priceIntToString($price)
    {
        $price = Price::fromFractions($price)->round($this->currency->getDefaultFractionDigits());
        return (new IntlFormatter($this->locale))->format(new Money($price->getAmount(), $this->currency));
    }

    /**
     * @param string $input
     * @return FacetFilterRange
     */
    public function decode($input)
    {
        if (!preg_match('/^([\d.]+)-([\d.]+)$/', $input, $range)) {
            throw new InvalidTransformationInputException(sprintf('Price range "%s" can not be decoded.', $input));
        }

        return FacetFilterRange::create($this->priceStringToInt($range[1]), $this->priceStringToInt($range[2]));
    }

    /**
     * @param string $price
     * @return int
     */
    private function priceStringToInt($price)
    {
        return Price::fromDecimalValue($price)->getAmount();
    }
}
