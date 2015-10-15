<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

class SearchEngineFacetField
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var SearchEngineFacetFieldValueCount[]
     */
    private $values;

    public function __construct(AttributeCode $attributeCode, SearchEngineFacetFieldValueCount ...$facetFieldValues)
    {
        $this->attributeCode = $attributeCode;
        $this->values = $facetFieldValues;
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return SearchEngineFacetFieldValueCount[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
