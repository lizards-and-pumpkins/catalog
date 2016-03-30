<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

interface FacetFilterRequestField
{
    /**
     * @return bool
     */
    public function isRanged();

    /**
     * @return AttributeCode
     */
    public function getAttributeCode();
}
