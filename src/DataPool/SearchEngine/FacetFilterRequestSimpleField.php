<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

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

    public function isRanged() : bool
    {
        return false;
    }

    public function getAttributeCode() : AttributeCode
    {
        return $this->attributeCode;
    }
}
