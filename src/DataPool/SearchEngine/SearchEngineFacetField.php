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
     * @var SearchEngineFacetFieldValue[]
     */
    private $values;

    public function __construct(AttributeCode $attributeCode, SearchEngineFacetFieldValue ...$facetFieldValue)
    {
        $this->attributeCode = $attributeCode;
        $this->values = $facetFieldValue;
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return SearchEngineFacetFieldValue[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
