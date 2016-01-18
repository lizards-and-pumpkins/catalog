<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;

interface PriceFacetFieldsBuilder
{
    /**
     * @return FacetFilterRequestField[]
     */
    public function createPriceFacetFields();
}
