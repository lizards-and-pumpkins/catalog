<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;

interface FacetFieldTransformation
{
    /**
     * @param FacetFilterRange|string $input
     * @return string
     */
    public function encode($input) : string;

    /**
     * @param string $input
     * @return mixed
     */
    public function decode(string $input);
}
