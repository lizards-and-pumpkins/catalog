<?php

namespace Brera\Product;

class FilterNavigationFilterValue
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
     * @return FilterNavigationFilterValue
     */
    public static function create($value, $count)
    {
        self::validateFilterValue($value);
        self::validateFilterCount($count);

        return new self($value, $count, false);
    }

    /**
     * @param string $value
     * @param int $count
     * @return FilterNavigationFilterValue
     */
    public static function createSelected($value, $count)
    {
        self::validateFilterValue($value);
        self::validateFilterCount($count);

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
    private static function validateFilterValue($value)
    {
        if (!is_string($value)) {
            throw new InvalidFilterNavigationFilterValueValueException(
                sprintf('Filter value must be a string, "%s" given.', gettype($value))
            );
        }
    }

    /**
     * @param int $count
     */
    private static function validateFilterCount($count)
    {
        if (!is_int($count)) {
            throw new InvalidFilterNavigationFilterValueCountException(
                sprintf('Filter count must be an integer, "%s" given.', gettype($count))
            );
        }
    }
}
