<?php

namespace Brera\Product;

class FilterNavigationFilterOption
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
     * @var bool
     */
    private $isSelected;

    /**
     * @param string $value
     * @param int $count
     * @param bool $isSelected
     */
    private function __construct($value, $count, $isSelected)
    {
        $this->value = $value;
        $this->count = $count;
        $this->isSelected = $isSelected;
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

        return new self($value, $count, false);
    }

    /**
     * @param string $value
     * @param int $count
     * @return FilterNavigationFilterOption
     */
    public static function createSelected($value, $count)
    {
        self::validateFilterOptionValue($value);
        self::validateFilterOptionCount($count);

        return new self($value, $count, true);
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
