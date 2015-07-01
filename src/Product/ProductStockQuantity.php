<?php

namespace Brera\Product;

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
    public function __construct($quantity)
    {
        if (!is_int($quantity)) {
            throw new InvalidStockSourceException(
                sprintf('Expecting integer stock source, got %s', gettype($quantity))
            );
        }

        $this->quantity = $quantity;
    }

    /**
     * @param string $quantityString
     * @return ProductStockQuantity
     * @throws InvalidStockSourceException
     */
    public static function fromString($quantityString)
    {
        if (!is_string($quantityString)) {
            throw new InvalidStockSourceException(
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
     * @param string $quantityString
     * @param int $numberOfDecimalPoints
     * @return int
     */
    private function multiplyByNumberOfDecimalPoints($quantityString, $numberOfDecimalPoints)
    {
        $base = pow(10, $numberOfDecimalPoints);
        $quantityFloat = round($quantityString, $numberOfDecimalPoints);

        return intval($quantityFloat * $base);
    }
}
