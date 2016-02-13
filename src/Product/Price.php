<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidNumberOfDecimalPointsForPriceException;

class Price
{
    const DEFAULT_DECIMAL_POINTS = 6;

    /**
     * @var int
     */
    private $fractions;

    /**
     * @var int
     */
    private $numDecimalPoints;

    /**
     * @param int $fractions
     * @param int $numDecimalPoints
     */
    private function __construct($fractions, $numDecimalPoints)
    {
        $this->validateNumberOfDecimalPoints($numDecimalPoints);

        $this->fractions = (int) $fractions;
        $this->numDecimalPoints = $numDecimalPoints;
    }

    /**
     * @param int $fractions
     * @param int $numDecimalPoints
     * @return static
     */
    public static function fromFractions($fractions, $numDecimalPoints = self::DEFAULT_DECIMAL_POINTS)
    {
        return new static((int) $fractions, $numDecimalPoints);
    }

    /**
     * @param string|float $amount
     * @param int $numDecimalPoints
     * @return static
     */
    public static function fromAmount($amount, $numDecimalPoints = self::DEFAULT_DECIMAL_POINTS)
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
    public function roundToFractions($numDecimalPoints)
    {
        $this->validateNumberOfDecimalPoints($numDecimalPoints);
        
        $base = pow(10, $this->numDecimalPoints);
        $roundedFractions = round($this->fractions / $base, $numDecimalPoints);
        return static::fromAmount($roundedFractions, $numDecimalPoints);
    }

    /**
     * @param int $numDecimalPoints
     */
    private function validateNumberOfDecimalPoints($numDecimalPoints)
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
}
