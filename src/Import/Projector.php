<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

interface Projector
{
    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData);
}
