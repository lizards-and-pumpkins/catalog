<?php

namespace Brera\DataPool;

use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\SnippetResult;
use Brera\SnippetResultList;

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

    public function writeSnippetResultList(SnippetResultList $snippetResultList)
    {
        /** @var SnippetResult $snippetResult */
        foreach ($snippetResultList as $snippetResult) {
            $this->keyValueStore->set($snippetResult->getKey(), $snippetResult->getContent());
        }
    }

    public function writeSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        $this->searchEngine->addSearchDocumentCollection($searchDocumentCollection);
    }
}
