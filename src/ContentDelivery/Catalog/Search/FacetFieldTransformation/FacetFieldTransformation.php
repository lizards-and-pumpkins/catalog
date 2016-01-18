<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

interface FacetFieldTransformation
{
    /**
     * @param FacetFilterRange $range
     * @return string
     */
    public function encode(FacetFilterRange $range);

    /**
     * @param string $input
     * @return FacetFilterRange
     */
    public function decode($input);
}
