<?php

namespace Brera;

use Brera\Environment\EnvironmentSource;

interface SnippetRendererCollection
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param EnvironmentSource $environment
     * @return SnippetResultList
     * @throws InvalidProjectionDataSourceType
     */
    public function render(ProjectionSourceData $dataObject, EnvironmentSource $environment);
}
