<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

class EuroPriceRangeTransformation implements FacetFieldTransformation
{
    const PRICE_BASE = 100;
    const DECIMAL_POINTS = 2;

    /**
     * {@inheritdoc}
     */
    public function encode($input)
    {
        if (!preg_match('/^(\d+)' . SearchEngine::RANGE_DELIMITER . '(\d+)$/', $input, $range)) {
            return $input;
        }

        return sprintf(
            '%s € - %s €',
            number_format($range[1] / self::PRICE_BASE, self::DECIMAL_POINTS, ',', '.'),
            number_format($range[2] / self::PRICE_BASE, self::DECIMAL_POINTS, ',', '.')
        );
    }

    /**
     * @param string $input
     * @return string
     */
    public function decode($input)
    {
        if (!preg_match('/^([\d,]+) € - ([\d,]+) €$/', $input, $range)) {
            return $input;
        }

        return sprintf(
            '%s%s%s',
            $this->priceStringToInt($range[1]),
            SearchEngine::RANGE_DELIMITER,
            $this->priceStringToInt($range[2])
        );
    }

    /**
     * @param string $price
     * @return int
     */
    private function priceStringToInt($price)
    {
        return round(str_replace(',', '.', $price) * self::PRICE_BASE);
    }
}
