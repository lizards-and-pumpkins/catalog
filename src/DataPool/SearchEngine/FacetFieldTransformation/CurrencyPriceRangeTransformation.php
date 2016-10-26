<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\Import\Price\Price;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\IntlFormatter;
use SebastianBergmann\Money\Money;

class CurrencyPriceRangeTransformation implements FacetFieldTransformation
{
    /**
     * @var callable
     */
    private $localeFactory;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var string
     */
    private $memoizedLocale;

    public function __construct(Currency $currency, callable $localeFactory)
    {
        $this->localeFactory = $localeFactory;
        $this->currency = $currency;
    }

    /**
     * @param FacetFilterRange|string $input
     * @return string
     */
    public function encode($input) : string
    {
        return $this->getFormattedPriceRangeString($input);
    }

    private function getFormattedPriceRangeString(FacetFilterRange $range) : string
    {
        return sprintf('%s - %s', $this->priceIntToString($range->from()), $this->priceIntToString($range->to()));
    }

    /**
     * @param int|string|float|null $price
     * @return string
     */
    private function priceIntToString($price) : string
    {
        $price = Price::fromFractions($price)->round($this->currency->getDefaultFractionDigits());
        return (new IntlFormatter($this->getLocale()))->format(new Money($price->getAmount(), $this->currency));
    }

    public function decode(string $input) : FacetFilterRange
    {
        if (!preg_match('/^([\d.]+)-([\d.]+)$/', $input, $range)) {
            throw new InvalidTransformationInputException(sprintf('Price range "%s" can not be decoded.', $input));
        }

        return FacetFilterRange::create($this->priceStringToInt($range[1]), $this->priceStringToInt($range[2]));
    }

    private function priceStringToInt(string $price) : int
    {
        return Price::fromDecimalValue($price)->getAmount();
    }

    private function getLocale() : string
    {
        if (! $this->memoizedLocale) {
            $this->memoizedLocale = call_user_func($this->localeFactory);
        }
        return $this->memoizedLocale;
    }
}
