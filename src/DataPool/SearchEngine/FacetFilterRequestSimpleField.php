<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

class FacetFilterRequestSimpleField implements FacetFilterRequestField
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    public function __construct(AttributeCode $attributeCode)
    {
        $this->attributeCode = $attributeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function isRanged()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }
}
