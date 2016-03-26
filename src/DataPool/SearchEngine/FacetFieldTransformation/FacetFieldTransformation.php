<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

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
