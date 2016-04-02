<?php

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubSearchEngine implements SearchEngine, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    public function addDocument(SearchDocument $searchDocument)
    {
        // Intentionally left empty
    }

    /**
     * @param SearchCriteria $criteria
     * @param QueryOptions $queryOptions
     * @return void
     */
    public function query(SearchCriteria $criteria, QueryOptions $queryOptions)
    {
        // Intentionally left empty
    }

    /**
     * @param string $searchString
     * @param QueryOptions $queryOptions
     * @return void
     */
    public function queryFullText($searchString, QueryOptions $queryOptions)
    {
        // Intentionally left empty
    }
}
