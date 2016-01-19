<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\Exception\InvalidTransformationInputException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

class EuroPriceRangeTransformation implements FacetFieldTransformation
{
    const PRICE_BASE = 100;
    const DECIMAL_POINTS = 2;

    /**
     * {@inheritdoc}
     */
    public function encode(FacetFilterRange $range)
    {
        return sprintf('%s € - %s €', $this->priceIntToString($range->from()), $this->priceIntToString($range->to()));
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
        return round(str_replace(',', '.', $price) * self::PRICE_BASE);
    }

    /**
     * @param int $price
     * @return string
     */
    private function priceIntToString($price)
    {
        return number_format($price / self::PRICE_BASE, self::DECIMAL_POINTS, ',', '.');
    }
}
