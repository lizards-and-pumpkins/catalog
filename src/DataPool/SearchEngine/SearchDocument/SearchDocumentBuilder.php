<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\Context\ContextSource;

interface SearchDocumentBuilder
{
    /**
     * @param mixed $projectionSourceDataData
     * @param ContextSource $contextSource
     */
    public function aggregate($projectionSourceDataData, ContextSource $contextSource);
}
