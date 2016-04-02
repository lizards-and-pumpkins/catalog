<?php

namespace LizardsAndPumpkins\Import;

interface Projector
{
    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData);
}
