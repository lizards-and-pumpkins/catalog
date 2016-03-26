<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

interface SearchEngine
{
    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addDocument(SearchDocument $searchDocument);

    /**
     * @param SearchCriteria $criteria
     * @param QueryOptions $queryOptions
     * @return SearchEngineResponse
     */
    public function query(SearchCriteria $criteria, QueryOptions $queryOptions);

    /**
     * @param string $searchString
     * @param QueryOptions $queryOptions
     * @return SearchEngineResponse
     */
    public function queryFullText($searchString, QueryOptions $queryOptions);
}
