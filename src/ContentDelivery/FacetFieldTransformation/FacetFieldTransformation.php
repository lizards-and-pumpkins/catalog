<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

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
     * @return string
     */
    public function decode($input);
}
