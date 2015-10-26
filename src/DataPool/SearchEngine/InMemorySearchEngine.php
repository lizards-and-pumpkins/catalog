<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        array_map(function (SearchDocument $searchDocument) {
            $this->index[$this->getSearchDocumentIdentifier($searchDocument)] = $searchDocument;
        }, $searchDocumentCollection->getDocuments());
    }

    /**
     * @return SearchDocument[]
     */
    final protected function getSearchDocuments()
    {
        return $this->index;
    }

    public function clear()
    {
        $this->index = [];
    }

    /**
     * @return SearchCriteriaBuilder
     */
    final protected function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }
}
