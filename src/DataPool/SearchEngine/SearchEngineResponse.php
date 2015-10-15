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
     * @var SearchEngineFacetFieldCollection
     */
    private $facetFieldCollection;

    /**
     * @var int
     */
    private $totalNumberOfResults;

    /**
     * @param SearchDocumentCollection $searchDocuments
     * @param SearchEngineFacetFieldCollection $facetFieldCollection
     * @param int $totalNumberOfResults
     */
    public function __construct(
        SearchDocumentCollection $searchDocuments,
        SearchEngineFacetFieldCollection $facetFieldCollection,
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
     * @return SearchEngineFacetFieldCollection
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
