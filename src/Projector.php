<?php

namespace Brera;

interface Projector
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param Environment $environment
     * @throws InvalidProjectionDataSourceType
     * @return null
     */
    public function project(ProjectionSourceData $dataObject, Environment $environment);
}
