<?php

declare(strict_types=1);

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

    public function getAttributeCode() : AttributeCode
    {
        return $this->attributeCode;
    }

    /**
     * @return FacetFieldValue[]
     */
    public function getValues() : array
    {
        return $this->values;
    }
}
