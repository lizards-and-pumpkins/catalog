<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\Product\FilterNavigationFilterCollection;

class SearchEngineResponse
{
    /**
     * @var SearchDocumentCollection
     */
    private $searchDocuments;

    /**
     * @var FilterNavigationFilterCollection
     */
    private $filterCollection;

    public function __construct(
        SearchDocumentCollection $searchDocuments,
        FilterNavigationFilterCollection $filterCollection
    ) {

        $this->searchDocuments = $searchDocuments;
        $this->filterCollection = $filterCollection;
    }

    /**
     * @return SearchDocumentCollection
     */
    public function getSearchDocuments()
    {
        return $this->searchDocuments;
    }

    /**
     * @return FilterNavigationFilterCollection
     */
    public function getFilterCollection()
    {
        return $this->filterCollection;
    }
}
