<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

interface FacetFieldTransformation
{
    /**
     * @param string $input
     * @return string
     */
    public function encode($input);
    /**
     * @param string $input
     * @return string
     */
    public function decode($input);
}
