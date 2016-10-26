<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
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

    public function query(SearchCriteria $criteria, QueryOptions $queryOptions) : SearchEngineResponse
    {
        // Intentionally left empty
    }

    public function queryFullText(string $searchString, QueryOptions $queryOptions) : SearchEngineResponse
    {
        // Intentionally left empty
    }
}
