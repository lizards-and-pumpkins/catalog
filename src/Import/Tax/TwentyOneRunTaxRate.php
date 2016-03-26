<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Tax\Exception\InvalidTaxRateException;
use LizardsAndPumpkins\Import\Tax\TaxService;

class TwentyOneRunTaxRate implements TaxService
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
     * @return TwentyOneRunTaxRate
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
     * @return int
     */
    public function getRate()
    {
        return (int) ($this->getFactor() * 100 - 100);
    }

    /**
     * @param Price $price
     * @return Price
     */
    public function applyTo(Price $price)
    {
        return $price->multiplyBy($this->getFactor());
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
     * @return string
     */
    private function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
