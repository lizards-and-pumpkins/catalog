<?php

namespace Brera;

use Brera\Context\ContextSource;

interface Projector
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $context
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $context);
}
