<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class SearchEngineFacetFieldCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var SearchEngineFacetField[]
     */
    private $facetFields;

    public function __construct(SearchEngineFacetField ...$facetFields)
    {
        $this->facetFields = $facetFields;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->facetFields);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->facetFields);
    }

    /**
     * @return SearchEngineFacetField[]
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * @return SearchEngineFacetField[]
     */
    function jsonSerialize()
    {
        return $this->facetFields;
    }
}
