<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidPriceSourceException;

class Price implements Money
{
    const NUM_DECIMAL_POINTS = 2;

    /**
     * @var int
     */
    private $amount;

    /**
     * @param int $amount
     */
    public function __construct($amount)
    {
        if (!is_int($amount)) {
            throw new InvalidPriceSourceException(sprintf('Can not create a price from %s', gettype($amount)));
        }

        $this->amount = $amount;
    }

    /**
     * @param string $amountString
     * @return Price
     */
    public static function fromString($amountString)
    {
        if (!is_string($amountString)) {
            throw new InvalidPriceSourceException(sprintf('Can not create a price from %s', gettype($amountString)));
        }

        $amountInt = self::convertStringPriceIntoInt($amountString, self::NUM_DECIMAL_POINTS);

        return new static($amountInt);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amountString
     * @param int $numDecimalPoints
     * @return int
     */
    private static function convertStringPriceIntoInt($amountString, $numDecimalPoints)
    {
        $base = pow(10, $numDecimalPoints);
        $priceFloat = round($amountString, $numDecimalPoints);

        return intval($priceFloat * $base);
    }
}
