<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\Utils\Clearable;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract implements Clearable
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    public function addSearchDocument(SearchDocument $searchDocument)
    {
        $this->index[$this->getSearchDocumentIdentifier($searchDocument)] = $searchDocument;
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
