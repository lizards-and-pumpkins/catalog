<?php

declare(strict_types=1);

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

    public function count() : int
    {
        return count($this->facetFields);
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->facetFields);
    }

    /**
     * @return FacetField[]
     */
    public function getFacetFields() : array
    {
        return $this->facetFields;
    }

    /**
     * @return FacetField[]
     */
    public function jsonSerialize() : array
    {
        return array_reduce($this->facetFields, function ($carry, FacetField $facetField) {
            return array_merge($carry, [(string) $facetField->getAttributeCode() => $facetField->getValues()]);
        }, []);
    }
}
