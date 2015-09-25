<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

interface SearchDocumentBuilder
{
    /**
     * @param mixed $projectionSourceDataData
     */
    public function aggregate($projectionSourceDataData);
}
