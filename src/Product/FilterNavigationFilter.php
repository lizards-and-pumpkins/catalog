<?php

namespace Brera\Product;

class FilterNavigationFilter
{
    /**
     * @var string
     */
    private $filterNavigationCode;

    /**
     * @var FilterNavigationFilterOptionCollection
     */
    private $filterValueCollection;

    /**
     * @param string $filterNavigationCode
     * @param FilterNavigationFilterOptionCollection $filterValueCollection
     */
    private function __construct($filterNavigationCode, FilterNavigationFilterOptionCollection $filterValueCollection)
    {
        $this->filterNavigationCode = $filterNavigationCode;
        $this->filterValueCollection = $filterValueCollection;
    }

    /**
     * @param string $filterNavigationCode
     * @param FilterNavigationFilterOptionCollection $filterValueCollection
     * @return FilterNavigationFilter
     */
    public static function create($filterNavigationCode, FilterNavigationFilterOptionCollection $filterValueCollection)
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
     * @return FilterNavigationFilterOptionCollection
     */
    public function getValuesCollection()
    {
        return $this->filterValueCollection;
    }
}
