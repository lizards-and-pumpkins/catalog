<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\Utils\Clearable;

class DataPoolWriter implements Clearable
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

    public function clear()
    {
        $this->clearInstance($this->searchEngine);
        $this->clearInstance($this->keyValueStore);
    }

    /**
     * @param object $instance
     */
    private function clearInstance($instance)
    {
        if ($instance instanceof Clearable) {
            $instance->clear();
        }
    }
}
