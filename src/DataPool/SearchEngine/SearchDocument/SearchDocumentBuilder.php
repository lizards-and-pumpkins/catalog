<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\ContextSource;
use Brera\ProjectionSourceData;

interface SearchDocumentBuilder
{
    /**
     * @param ProjectionSourceData $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function aggregate(ProjectionSourceData $projectionSourceData, ContextSource $contextSource);
}
