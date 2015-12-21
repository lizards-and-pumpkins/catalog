<?php

namespace LizardsAndPumpkins\Projection;

interface Projector
{
    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData);
}
