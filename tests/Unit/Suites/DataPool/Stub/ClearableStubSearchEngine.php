<?php


namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Utils\Clearable;

class ClearableStubSearchEngine implements SearchEngine, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        // Intentionally left empty
    }

    /**
     * @param SearchCriteria $criteria
     * @param array $filterSelection
     * @param Context $context
     * @param string[] $facetFiltersConfig
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return void
     */
    public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        array $filterSelection,
        Context $context,
        array $facetFiltersConfig,
        $rowsPerPage,
        $pageNumber
    ) {
        // Intentionally left empty
    }
}
