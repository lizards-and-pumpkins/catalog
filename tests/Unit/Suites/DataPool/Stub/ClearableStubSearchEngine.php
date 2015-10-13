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
     * @param string $queryString
     * @param Context $context
     * @param string[] $facetFields
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return void
     */
    public function query($queryString, Context $context, array $facetFields, $rowsPerPage, $pageNumber)
    {
        // Intentionally left empty
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @param string[] $facetFields
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return void
     */
    public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        array $facetFields,
        $rowsPerPage,
        $pageNumber
    ) {
        // Intentionally left empty
    }
}
