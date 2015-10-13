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

    public function __construct(
        SearchDocumentCollection $searchDocuments,
        SearchEngineFacetFieldCollection $facetFieldCollection
    ) {

        $this->searchDocuments = $searchDocuments;
        $this->facetFieldCollection = $facetFieldCollection;
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
}
