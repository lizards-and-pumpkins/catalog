<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    public function addSearchDocument(SearchDocument $searchDocument)
    {
        $this->index[] = $searchDocument;
    }

    /**
     * @return SearchDocument[]
     */
    protected function getSearchDocuments()
    {
        return $this->index;
    }
}
