<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionCountException;
use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterOptionValueException;

class FilterNavigationFilterOption implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $count;

    /**
     * @param string $value
     * @param int $count
     */
    private function __construct($value, $count)
    {
        $this->value = $value;
        $this->count = $count;
    }

    /**
     * @param string $value
     * @param int $count
     * @return FilterNavigationFilterOption
     */
    public static function create($value, $count)
    {
        self::validateFilterOptionValue($value);
        self::validateFilterOptionCount($count);

        return new self($value, $count);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param string $value
     */
    private static function validateFilterOptionValue($value)
    {
        if (!is_string($value) && !is_int($value)) {
            throw new InvalidFilterNavigationFilterOptionValueException(
                sprintf('Filter option value must be either string or integer, "%s" given.', gettype($value))
            );
        }
    }

    /**
     * @param int $count
     */
    private static function validateFilterOptionCount($count)
    {
        if (!is_int($count)) {
            throw new InvalidFilterNavigationFilterOptionCountException(
                sprintf('Filter option count must be an integer, "%s" given.', gettype($count))
            );
        }
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'value' => $this->value,
            'count' => $this->count,
        ];
    }
}
