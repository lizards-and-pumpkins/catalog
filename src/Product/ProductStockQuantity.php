<?php

namespace Brera\Product;

use Brera\Product\Exception\InvalidStockQuantitySourceException;

class ProductStockQuantity implements Quantity
{
    const NUM_DECIMAL_POINTS = 0;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param int $quantity
     */
    private function __construct($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @param int $quantityInt
     * @return ProductStockQuantity
     */
    public static function fromInt($quantityInt)
    {
        if (!is_int($quantityInt)) {
            throw new InvalidStockQuantitySourceException(
                sprintf('Expecting integer stock source, got %s', gettype($quantityInt))
            );
        }

        $quantity = self::multiplyByNumberOfDecimalPoints($quantityInt, self::NUM_DECIMAL_POINTS);

        return new static($quantity);
    }

    /**
     * @param string $quantityString
     * @return ProductStockQuantity
     */
    public static function fromString($quantityString)
    {
        if (!is_string($quantityString)) {
            throw new InvalidStockQuantitySourceException(
                sprintf('Expecting string stock source, got %s', gettype($quantityString))
            );
        }

        $quantity = self::multiplyByNumberOfDecimalPoints($quantityString, self::NUM_DECIMAL_POINTS);

        return new static($quantity);
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string|int $quantity
     * @param int $numberOfDecimalPoints
     * @return int
     */
    private static function multiplyByNumberOfDecimalPoints($quantity, $numberOfDecimalPoints)
    {
        $base = pow(10, $numberOfDecimalPoints);
        $quantityFloat = round($quantity, $numberOfDecimalPoints);

        return intval($quantityFloat * $base);
    }
}
