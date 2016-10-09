<?php

declare(strict_types=1);

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
     * @param int|string $fractions
     * @param int $numDecimalPlaces
     */
    private function __construct($fractions, $numDecimalPlaces)
    {
        $this->validateNumberOfDecimalPlaces($numDecimalPlaces);

        $this->fractions = (int) $fractions;
        $this->numDecimalPlaces = $numDecimalPlaces;
    }

    /**
     * @param int|string $fractions
     * @return Price
     */
    public static function fromFractions($fractions) : Price
    {
        return static::fromFractionsWithDecimalPlaces($fractions, self::DEFAULT_DECIMAL_PLACES);
    }

    /**
     * @param int|string $fractions
     * @param int $numDecimalPoints
     * @return Price
     */
    public static function fromFractionsWithDecimalPlaces($fractions, int $numDecimalPoints) : Price
    {
        return new static($fractions, $numDecimalPoints);
    }

    /**
     * @param string|float $amount
     * @return Price
     */
    public static function fromDecimalValue($amount) : Price
    {
        return self::fromDecimalValueWithPrecision($amount, static::DEFAULT_DECIMAL_PLACES);
    }

    /**
     * @param string|float $amount
     * @param int $numDecimalPoints
     * @return Price
     */
    public static function fromDecimalValueWithPrecision($amount, int $numDecimalPoints) : Price
    {
        $fractions = self::convertFloatToIntegerAmount((string) $amount, $numDecimalPoints);
        return new static($fractions, $numDecimalPoints);
    }

    private static function convertFloatToIntegerAmount(string $amountFloat, int $numDecimalPoints) : int
    {
        $roundedAmount = round($amountFloat, $numDecimalPoints);
        $base = pow(10, $numDecimalPoints);

        return intval(($roundedAmount * $base) + .0000000001);
    }

    public function getAmount() : int
    {
        return $this->fractions;
    }

    public function __toString() : string
    {
        return (string) $this->fractions;
    }

    public function round(int $numDecimalPoints) : Price
    {
        $this->validateNumberOfDecimalPlaces($numDecimalPoints);
        
        $base = pow(10, $this->numDecimalPlaces);
        $roundedFractions = round($this->fractions / $base, $numDecimalPoints);
        return static::fromDecimalValueWithPrecision($roundedFractions, $numDecimalPoints);
    }

    private function validateNumberOfDecimalPlaces(int $numDecimalPoints)
    {
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
    public function multiplyBy($factor) : Price
    {
        $multipliedAmount = (int) round($this->getAmount() * $factor, 0, PHP_ROUND_HALF_DOWN);
        return static::fromFractionsWithDecimalPlaces($multipliedAmount, $this->numDecimalPlaces);
    }
}
