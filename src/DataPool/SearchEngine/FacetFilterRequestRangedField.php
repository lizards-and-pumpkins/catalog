<?php

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

    /**
     * {@inheritdoc}
     */
    public function isRanged()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return FacetFilterRange[]
     */
    public function getRanges()
    {
        return $this->ranges;
    }
}
