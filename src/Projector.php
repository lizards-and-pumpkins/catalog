<?php

namespace Brera;

use Brera\Environment\EnvironmentSource;

interface Projector
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param EnvironmentSource $environment
     * @throws InvalidProjectionDataSourceType
     * @return null
     */
    public function project(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
