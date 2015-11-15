<?php

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\ContentDelivery\Catalog\FacetFilterConfigCollection;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
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
     * @param FacetFilterConfigCollection $facetFilterConfigCollection
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortOrderConfig $sortOrderConfig
     * @return void
     */
    public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        array $filterSelection,
        Context $context,
        FacetFilterConfigCollection $facetFilterConfigCollection,
        $rowsPerPage,
        $pageNumber,
        SortOrderConfig $sortOrderConfig
    ) {
        // Intentionally left empty
    }
}
