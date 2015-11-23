<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

class SearchEngineResponse
{
    /**
     * @var SearchDocumentCollection
     */
    private $searchDocuments;

    /**
     * @var FacetFieldCollection
     */
    private $facetFieldCollection;

    /**
     * @var int
     */
    private $totalNumberOfResults;

    /**
     * @param SearchDocumentCollection $searchDocuments
     * @param FacetFieldCollection $facetFieldCollection
     * @param int $totalNumberOfResults
     */
    public function __construct(
        SearchDocumentCollection $searchDocuments,
        FacetFieldCollection $facetFieldCollection,
        $totalNumberOfResults
    ) {
        $this->searchDocuments = $searchDocuments;
        $this->facetFieldCollection = $facetFieldCollection;
        $this->totalNumberOfResults = $totalNumberOfResults;
    }

    /**
     * @return SearchDocumentCollection
     */
    public function getSearchDocuments()
    {
        return $this->searchDocuments;
    }

    /**
     * @return FacetFieldCollection
     */
    public function getFacetFieldCollection()
    {
        return $this->facetFieldCollection;
    }

    /**
     * @return int
     */
    public function getTotalNumberOfResults()
    {
        return $this->totalNumberOfResults;
    }
}
