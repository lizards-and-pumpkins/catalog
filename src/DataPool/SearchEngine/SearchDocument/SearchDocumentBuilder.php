<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

use LizardsAndPumpkins\Context\ContextSource;

interface SearchDocumentBuilder
{
    /**
     * @param mixed $projectionSourceDataData
     * @param ContextSource $contextSource
     */
    public function aggregate($projectionSourceDataData, ContextSource $contextSource);
}
