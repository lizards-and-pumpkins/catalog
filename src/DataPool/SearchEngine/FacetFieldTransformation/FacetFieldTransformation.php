<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation;

interface FacetFieldTransformation
{
    /**
     * @param mixed $input
     * @return string
     */
    public function encode($input);

    /**
     * @param string $input
     * @return mixed
     */
    public function decode($input);
}
