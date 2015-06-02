<?php

namespace Brera\DataPool;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Snippet;
use Brera\SnippetList;

class DataPoolWriter
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    public function __construct(KeyValueStore $keyValueStore, SearchEngine $searchEngine)
    {
        $this->keyValueStore = $keyValueStore;
        $this->searchEngine = $searchEngine;
    }

    public function writeSnippetList(SnippetList $snippetList)
    {
        foreach ($snippetList as $snippet) {
            $this->writeSnippet($snippet);
        }
    }

    public function writeSnippet(Snippet $snippet)
    {
        $this->keyValueStore->set($snippet->getKey(), $snippet->getContent());
    }

    public function writeSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        $this->searchEngine->addSearchDocumentCollection($searchDocumentCollection);
    }
}
