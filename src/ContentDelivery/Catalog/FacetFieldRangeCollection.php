<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

class FacetFieldRangeCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var FacetFieldRange[]
     */
    private $facetFieldRanges;

    public function __construct(FacetFieldRange ...$facetFieldRanges)
    {
        $this->facetFieldRanges = $facetFieldRanges;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->facetFieldRanges);
    }

    /**
     * @return FacetFieldRange[]
     */
    public function getRanges()
    {
        return $this->facetFieldRanges;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->facetFieldRanges);
    }
}
