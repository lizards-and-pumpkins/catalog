<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        array_map(function (SearchDocument $searchDocument) {
            $this->index[$this->getSearchDocumentIdentifier($searchDocument)] = $searchDocument;
        }, $searchDocumentCollection->getDocuments());
    }

    /**
     * @return SearchDocument[]
     */
    protected function getSearchDocuments()
    {
        return $this->index;
    }

    public function clear()
    {
        $this->index = [];
    }
}
