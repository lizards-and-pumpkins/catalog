<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

class FacetFieldTransformationCollection implements \IteratorAggregate
{
    /**
     * @var FacetFieldTransformation[]
     */
    private $facetFieldTransformations;

    public function __construct(FacetFieldTransformation ...$facetFieldTransformations)
    {
        $this->facetFieldTransformations = $facetFieldTransformations;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->facetFieldTransformations);
    }

    /**
     * @return FacetFieldTransformation[]
     */
    public function getTransformations()
    {
        return $this->facetFieldTransformations;
    }
}
