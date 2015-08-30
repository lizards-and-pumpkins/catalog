<?php

namespace Brera\Product;

use Brera\Http\HttpRequest;

class FilterNavigationFilterCollection implements \Countable
{
    /**
     * @var FilterNavigationFilter[]
     */
    private $filters = [];

    /**
     * @var string[]
     */
    private $allowedFilterCodes;

    /**
     * @param string[] $allowedFilterCodes
     */
    public function __construct(array $allowedFilterCodes)
    {
        $this->allowedFilterCodes = $allowedFilterCodes;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->filters);
    }

    /**
     * @return FilterNavigationFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function fillFromRequest(HttpRequest $request)
    {
        foreach ($this->allowedFilterCodes as $filterCode) {
            $rawSelectedFilterValues = $request->getQueryParameter($filterCode);
            $selectedFilterValues = explode(',', $rawSelectedFilterValues);
            $filter = FilterNavigationFilter::create($filterCode, array_filter($selectedFilterValues));
            $this->filters[$filter->getCode()] = $filter;
        }
    }
}
