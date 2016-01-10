<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

interface SearchEngine
{
    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addDocument(SearchDocument $searchDocument);

    /**
     * @param SearchCriteria $criteria
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortOrderConfig $sortOrderConfig
     * @return SearchEngineResponse
     */
    public function query(
        SearchCriteria $criteria,
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        $rowsPerPage,
        $pageNumber,
        SortOrderConfig $sortOrderConfig
    );
}
