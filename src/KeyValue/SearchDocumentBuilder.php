<?php

namespace Brera\KeyValue;

use Brera\Environment\EnvironmentSource;
use Brera\ProjectionSourceData;

interface SearchDocumentBuilder
{
    /**
     * @param ProjectionSourceData $projectionSourceData
     * @param EnvironmentSource $environmentSource
     */
    public function aggregate(ProjectionSourceData $projectionSourceData, EnvironmentSource $environmentSource);
}
