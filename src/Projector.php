<?php

namespace Brera;

use Brera\Context\ContextSource;

interface Projector
{
    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource);
}
