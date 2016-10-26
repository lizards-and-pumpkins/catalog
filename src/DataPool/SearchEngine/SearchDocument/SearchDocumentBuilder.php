<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

interface SearchDocumentBuilder
{
    /**
     * @param mixed $projectionSourceDataData
     * @return SearchDocument
     */
    public function aggregate($projectionSourceDataData) : SearchDocument;
}
