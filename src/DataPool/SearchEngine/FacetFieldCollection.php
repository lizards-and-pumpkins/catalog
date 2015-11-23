<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class FacetFieldCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var FacetField[]
     */
    private $facetFields;

    public function __construct(FacetField ...$facetFields)
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
     * @return FacetField[]
     */
    public function getFacetFields()
    {
        return $this->facetFields;
    }

    /**
     * @return FacetField[]
     */
    public function jsonSerialize()
    {
        return array_reduce($this->facetFields, function ($carry, FacetField $facetField) {
            return array_merge($carry, [(string) $facetField->getAttributeCode() => $facetField->getValues()]);
        }, []);
    }
}
