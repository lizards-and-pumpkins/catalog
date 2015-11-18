<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Product\Price;

class FilterNavigationPriceRangesBuilder
{
    /**
     * @return array[]
     */
    public static function getPriceRanges()
    {
        $base = pow(10, Price::NUM_DECIMAL_POINTS);
        $rangeStep = 20 * $base;
        $rangesTo = 500 * $base;

        $priceRanges = [['from' => '*', 'to' => $rangeStep - 1]];
        for ($i = $rangeStep; $i < $rangesTo; $i += $rangeStep) {
            $priceRanges[] = ['from' => $i, 'to' => $i + $rangeStep - 1];
        }
        $priceRanges[] = ['from' => $rangesTo, 'to' => '*'];

        return $priceRanges;
    }
}
