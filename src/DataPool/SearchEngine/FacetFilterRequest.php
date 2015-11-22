<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class FacetFilterRequest
{
    /**
     * @var FacetFilterRequestField[]
     */
    private $fields;

    public function __construct(FacetFilterRequestField ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return FacetFilterRequestField[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
