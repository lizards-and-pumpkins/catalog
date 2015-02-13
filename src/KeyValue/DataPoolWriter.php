<?php

namespace Brera\KeyValue;

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

    /**
     * @param KeyValueStore $keyValueStore
     */
    public function __construct(KeyValueStore $keyValueStore, SearchEngine $searchEngine)
    {
        $this->keyValueStore = $keyValueStore;
        $this->searchEngine = $searchEngine;
    }

    /**
     * @param SnippetResultList $snippetResultList
     * @return void
     */
    public function writeSnippetResultList(SnippetResultList $snippetResultList)
    {
        /** @var SnippetResult $snippetResult */
        foreach ($snippetResultList as $snippetResult) {
            $this->keyValueStore->set($snippetResult->getKey(), $snippetResult->getContent());
        }
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function writeSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        $this->searchEngine->addSearchDocumentCollection($searchDocumentCollection);
    }
}
