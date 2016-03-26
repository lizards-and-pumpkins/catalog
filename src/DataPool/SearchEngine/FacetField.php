<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

class FacetField
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var FacetFieldValue[]
     */
    private $values;

    public function __construct(AttributeCode $attributeCode, FacetFieldValue ...$facetFieldValues)
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
     * @return FacetFieldValue[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
