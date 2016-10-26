<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

class FacetFilterRequestRangedField implements FacetFilterRequestField
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var FacetFilterRange[]
     */
    private $ranges;

    public function __construct(AttributeCode $attributeCode, FacetFilterRange ...$ranges)
    {
        $this->attributeCode = $attributeCode;
        $this->ranges = $ranges;
    }

    public function isRanged() : bool
    {
        return true;
    }

    public function getAttributeCode() : AttributeCode
    {
        return $this->attributeCode;
    }

    /**
     * @return FacetFilterRange[]
     */
    public function getRanges() : array
    {
        return $this->ranges;
    }
}
