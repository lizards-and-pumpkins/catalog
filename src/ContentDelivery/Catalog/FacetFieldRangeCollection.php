<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

class FacetFieldRangeCollection implements \IteratorAggregate
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
}
