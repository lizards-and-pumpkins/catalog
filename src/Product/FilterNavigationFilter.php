<?php

namespace Brera\Product;

class FilterNavigationFilter
{
    /**
     * @var string
     */
    private $filterNavigationCode;

    /**
     * @var FilterNavigationFilterValueCollection
     */
    private $filterValueCollection;

    /**
     * @param string $filterNavigationCode
     * @param FilterNavigationFilterValueCollection $filterValueCollection
     */
    private function __construct($filterNavigationCode, FilterNavigationFilterValueCollection $filterValueCollection)
    {
        $this->filterNavigationCode = $filterNavigationCode;
        $this->filterValueCollection = $filterValueCollection;
    }

    /**
     * @param string $filterNavigationCode
     * @param FilterNavigationFilterValueCollection $filterValueCollection
     * @return FilterNavigationFilter
     */
    public static function create($filterNavigationCode, FilterNavigationFilterValueCollection $filterValueCollection)
    {
        if (!is_string($filterNavigationCode)) {
            throw new InvalidFilterNavigationFilterCode(
                sprintf('Filter navigation filter code must be a string, got "%s".', gettype($filterNavigationCode))
            );
        }

        return new self($filterNavigationCode, $filterValueCollection);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->filterNavigationCode;
    }

    /**
     * @return FilterNavigationFilterValueCollection
     */
    public function getValuesCollection()
    {
        return $this->filterValueCollection;
    }
}
