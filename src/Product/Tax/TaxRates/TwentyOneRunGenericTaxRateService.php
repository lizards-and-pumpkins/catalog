<?php

namespace LizardsAndPumpkins\Product\Tax\TaxRates;

use LizardsAndPumpkins\Product\Tax\TaxRates\Exception\InvalidTaxRateException;

class TwentyOneRunGenericTaxRateService extends TwentyOneRunTaxRate
{
    /**
     * @var int
     */
    private $rate;

    /**
     * @param int $rate
     */
    private function __construct($rate)
    {
        $this->validateRate($rate);
        $this->rate = $rate;
    }

    /**
     * @param int $rate
     * @return TwentyOneRunGenericTaxRateService
     */
    public static function fromInt($rate)
    {
        return new self($rate);
    }

    /**
     * @param int $rate
     */
    private function validateRate($rate)
    {
        if (!is_int($rate)) {
            $message = sprintf('The tax rate has to be an integer value, got "%s"', $this->getType($rate));
            throw new InvalidTaxRateException($message);
        }
        if (0 === $rate) {
            throw new InvalidTaxRateException('The tax rate must not be zero');
        }
    }

    /**
     * @return float
     */
    final protected function getFactor()
    {
        return 1 + $this->rate / 100;
    }

    /**
     * @param mixed $variable
     */
    private function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
