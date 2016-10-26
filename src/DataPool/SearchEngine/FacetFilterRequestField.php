<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\AttributeCode;

interface FacetFilterRequestField
{
    public function isRanged() : bool;

    public function getAttributeCode() : AttributeCode;
}
