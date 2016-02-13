<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\Product\Price;

class FilterNavigationPriceRangesBuilder
{
    /**
     * @return FacetFilterRange[]
     */
    public static function getPriceRanges()
    {
        $base = pow(10, Price::DEFAULT_DECIMAL_PLACES);
        $rangeStep = 20 * $base;
        $rangesTo = 500 * $base;

        $priceRanges = [FacetFilterRange::create(null, $rangeStep - 1)];
        for ($i = $rangeStep; $i < $rangesTo; $i += $rangeStep) {
            $priceRanges[] = FacetFilterRange::create($i, $i + $rangeStep - 1);
        }
        $priceRanges[] = FacetFilterRange::create($rangesTo, null);

        return $priceRanges;
    }
}
