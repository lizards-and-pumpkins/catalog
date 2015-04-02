<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    /**
     * @param SearchDocument $searchDocument
     */
    public function addSearchDocument(SearchDocument $searchDocument)
    {
        array_push($this->index, $searchDocument);
    }

    /**
     * @return SearchDocument[]
     */
    protected function getSearchDocuments()
    {
        return $this->index;
    }
}
