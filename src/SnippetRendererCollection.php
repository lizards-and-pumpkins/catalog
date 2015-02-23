<?php

namespace Brera;

use Brera\Context\ContextSource;

interface SnippetRendererCollection
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $context
     * @return SnippetResultList
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function render(ProjectionSourceData $dataObject, ContextSource $context);
}
