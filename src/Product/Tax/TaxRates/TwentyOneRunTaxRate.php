<?php

namespace LizardsAndPumpkins\Product\Tax\TaxRates;

use LizardsAndPumpkins\Product\Tax\TaxService;

abstract class TwentyOneRunTaxRate implements TaxService
{
    /**
     * @param int|string $rate
     */
    public static function create($rate)
    {
        return new TwentyOneRunGenericTaxRateService($rate);
    }
    
    /**
     * @return float
     */
    abstract protected function getFactor();

    /**
     * @return int
     */
    public function getRate()
    {
        return (int) ($this->getFactor() * 100 - 100);
    }

    /**
     * @param int $price
     * @return int
     */
    public function apply($price)
    {
        $result = round($price * $this->getFactor(), 0, PHP_ROUND_HALF_DOWN);
        return (int) $result;
    }
}
