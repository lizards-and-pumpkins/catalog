<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

interface SearchEngine
{
    public function addDocument(SearchDocument $searchDocument);

    public function query(SearchCriteria $criteria, QueryOptions $queryOptions) : SearchEngineResponse;

    public function queryFullText(string $searchString, QueryOptions $queryOptions) : SearchEngineResponse;
}
