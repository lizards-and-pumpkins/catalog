<?php

namespace LizardsAndPumpkins\Product;

class FilterNavigationFilterOptionCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var FilterNavigationFilterOption[]
     */
    private $filterOptions = [];

    public function __construct(FilterNavigationFilterOption ...$filterOptions)
    {
        $this->filterOptions = $filterOptions;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->filterOptions);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->filterOptions);
    }

    /**
     * @return FilterNavigationFilterOption[]
     */
    public function getOptions()
    {
        return $this->filterOptions;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return $this->filterOptions;
    }
}
