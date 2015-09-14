<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextSource;

interface Projector
{
    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource);
}
