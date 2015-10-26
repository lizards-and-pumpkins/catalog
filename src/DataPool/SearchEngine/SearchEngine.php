<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

interface SearchEngine
{
    const RANGE_DELIMITER = ' TO ';
    const RANGE_WILDCARD = '*';

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection);

    /**
     * @param SearchCriteria $criteria
     * @param array[] $filterSelection
     * @param Context $context
     * @param string[] $facetFiltersConfig
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return SearchEngineResponse
     */
    public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        array $filterSelection,
        Context $context,
        array $facetFiltersConfig,
        $rowsPerPage,
        $pageNumber
    );
}
