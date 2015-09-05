<?php

namespace Brera\Product;

class FilterNavigationFilterValueCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterNavigationFilterValue[]
     */
    private $filterValues = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->filterValues);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->filterValues);
    }

    public function add(FilterNavigationFilterValue $filterValue)
    {
        $this->filterValues[] = $filterValue;
    }

    /**
     * @return FilterNavigationFilterValue[]
     */
    public function getFilterValues()
    {
        return $this->filterValues;
    }
}
