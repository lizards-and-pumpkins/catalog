<?php

namespace Brera\DataPool\SearchEngine;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

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
