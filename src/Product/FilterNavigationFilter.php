<?php

namespace Brera\Product;

class FilterNavigationFilter
{
    /**
     * @var string
     */
    private $filterNavigationCode;

    /**
     * @var string[]
     */
    private $selectedFilters;

    /**
     * @param string $filterNavigationCode
     * @param string[] $selectedFilters
     */
    private function __construct($filterNavigationCode, array $selectedFilters)
    {
        $this->filterNavigationCode = $filterNavigationCode;
        $this->selectedFilters = $selectedFilters;
    }

    /**
     * @param string $filterNavigationCode
     * @param string[] $selectedFilters
     * @return FilterNavigationFilter
     */
    public static function create($filterNavigationCode, array $selectedFilters)
    {
        if (!is_string($filterNavigationCode)) {
            throw new InvalidFilterNavigationFilterCode(
                sprintf('Filter navigation filter code must be a string, got "%s".', gettype($filterNavigationCode))
            );
        }

        return new self($filterNavigationCode, $selectedFilters);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->filterNavigationCode;
    }

    /**
     * @return string[]
     */
    public function getSelectedFilters()
    {
        return $this->selectedFilters;
    }
}
