<?php

namespace Brera\Product;

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

        $amountInt = intval(round($amountString, self::NUM_DECIMAL_POINTS) * pow(10, self::NUM_DECIMAL_POINTS));

        return new static($amountInt);
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
