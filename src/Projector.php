<?php

namespace Brera;

use Brera\Context\ContextSource;

interface Projector
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $contextSource
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $contextSource);
}
