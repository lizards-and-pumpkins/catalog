<?php

namespace Brera;

use Brera\Context\ContextSource;

interface Projector
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $context
     * @throws InvalidProjectionDataSourceTypeException
     * @return null
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $context);
}
