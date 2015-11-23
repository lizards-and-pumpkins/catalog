<?php
namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\AttributeCode;

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
