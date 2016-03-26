<?php

namespace LizardsAndPumpkins\Import\Price;

use LizardsAndPumpkins\Import\Price\Exception\InvalidNumberOfDecimalPointsForPriceException;

class Price
{
    const DEFAULT_DECIMAL_PLACES = 4;

    /**
     * @var int
     */
    private $fractions;

    /**
     * @var int
     */
    private $numDecimalPlaces;

    /**
     * @param int $fractions
     * @param int $numDecimalPlaces
     */
    private function __construct($fractions, $numDecimalPlaces)
    {
        $this->validateNumberOfDecimalPlaces($numDecimalPlaces);

        $this->fractions = (int) $fractions;
        $this->numDecimalPlaces = $numDecimalPlaces;
    }

    /**
     * @param int $fractions
     * @return Price
     */
    public static function fromFractions($fractions)
    {
        return static::fromFractionsWithDecimalPlaces($fractions, self::DEFAULT_DECIMAL_PLACES);
    }

    /**
     * @param int $fractions
     * @param int $numDecimalPoints
     * @return Price
     */
    public static function fromFractionsWithDecimalPlaces($fractions, $numDecimalPoints)
    {
        return new static($fractions, $numDecimalPoints);
    }

    /**
     * @param string|float $amount
     * @return Price
     */
    public static function fromDecimalValue($amount)
    {
        return self::fromDecimalValueWithPrecision($amount, static::DEFAULT_DECIMAL_PLACES);
    }

    /**
     * @param string|float $amount
     * @param int $numDecimalPoints
     * @return Price
     */
    public static function fromDecimalValueWithPrecision($amount, $numDecimalPoints)
    {
        $fractions = self::convertFloatToIntegerAmount((float) $amount, $numDecimalPoints);
        return new static($fractions, $numDecimalPoints);
    }

    /**
     * @param string $amountFloat
     * @param int $numDecimalPoints
     * @return int
     */
    private static function convertFloatToIntegerAmount($amountFloat, $numDecimalPoints)
    {
        $roundedAmount = round($amountFloat, $numDecimalPoints);
        $base = pow(10, $numDecimalPoints);

        return intval(($roundedAmount * $base) + .0000000001);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->fractions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->fractions;
    }

    /**
     * @param int $numDecimalPoints
     * @return Price
     */
    public function round($numDecimalPoints)
    {
        $this->validateNumberOfDecimalPlaces($numDecimalPoints);
        
        $base = pow(10, $this->numDecimalPlaces);
        $roundedFractions = round($this->fractions / $base, $numDecimalPoints);
        return static::fromDecimalValueWithPrecision($roundedFractions, $numDecimalPoints);
    }

    /**
     * @param int $numDecimalPoints
     */
    private function validateNumberOfDecimalPlaces($numDecimalPoints)
    {
        if (!is_int($numDecimalPoints)) {
            $type = gettype($numDecimalPoints);
            $nonIntErrorMessage = sprintf(
                'The number of decimal points for a price have to be specified as an integer, got %s',
                $type
            );
            throw new InvalidNumberOfDecimalPointsForPriceException($nonIntErrorMessage);
        }
        if ($numDecimalPoints < 0) {
            $isNegativeMessage = sprintf(
                'The number of decimal points for a price have to be specified as a positive integer, got %d',
                $numDecimalPoints
            );
            throw new InvalidNumberOfDecimalPointsForPriceException($isNegativeMessage);
        }
    }

    /**
     * @param float|int $factor
     * @return Price
     */
    public function multiplyBy($factor)
    {
        $multipliedAmount = round($this->getAmount() * $factor, 0, PHP_ROUND_HALF_DOWN);
        return static::fromFractionsWithDecimalPlaces($multipliedAmount, $this->numDecimalPlaces);
    }
}
