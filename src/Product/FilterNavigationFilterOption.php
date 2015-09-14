<?php

namespace Brera\Product;

use Brera\Product\Exception\InvalidFilterNavigationFilterOptionCountException;
use Brera\Product\Exception\InvalidFilterNavigationFilterOptionValueException;

class FilterNavigationFilterOption
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $count;

    /**
     * @var bool
     */
    private $isSelected;

    /**
     * @param string $code
     * @param string $value
     * @param int $count
     * @param bool $isSelected
     */
    private function __construct($code, $value, $count, $isSelected)
    {
        $this->code = $code;
        $this->value = $value;
        $this->count = $count;
        $this->isSelected = $isSelected;
    }

    /**
     * @param string $code
     * @param string $value
     * @param int $count
     * @return FilterNavigationFilterOption
     */
    public static function create($code, $value, $count)
    {
        self::validateFilterOptionValue($value);
        self::validateFilterOptionCount($count);

        return new self($code, $value, $count, false);
    }

    /**
     * @param string $code
     * @param string $value
     * @param int $count
     * @return FilterNavigationFilterOption
     */
    public static function createSelected($code, $value, $count)
    {
        self::validateFilterOptionValue($value);
        self::validateFilterOptionCount($count);

        return new self($code, $value, $count, true);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * @return bool
     */
    public function isSelected()
    {
        return $this->isSelected;
    }

    /**
     * @param string $value
     */
    private static function validateFilterOptionValue($value)
    {
        if (!is_string($value)) {
            throw new InvalidFilterNavigationFilterOptionValueException(
                sprintf('Filter option value must be a string, "%s" given.', gettype($value))
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
}
