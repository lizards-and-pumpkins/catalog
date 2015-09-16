<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Projection\UrlKeyForContext;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;
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
    
    /**
     * @var UrlKeyStore
     */
    private $urlKeyStorage;

    public function __construct(KeyValueStore $keyValueStore, SearchEngine $searchEngine, UrlKeyStore $urlKeyStorage)
    {
        $this->keyValueStore = $keyValueStore;
        $this->searchEngine = $searchEngine;
        $this->urlKeyStorage = $urlKeyStorage;
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
        $this->clearInstance($this->urlKeyStorage);
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

    public function writeUrlKeyCollection(UrlKeyForContextCollection $urlKeysForContextsCollection)
    {
        array_map(function (UrlKeyForContext $urlKeyForContext) {
            $version = (string) $urlKeyForContext->getContextValue(VersionedContext::CODE);
            $urlKey = (string) $urlKeyForContext->getUrlKey();
            $context = $urlKeyForContext->getContextAsString();
            $this->urlKeyStorage->addUrlKeyForVersion($version, $urlKey, $context);
        }, $urlKeysForContextsCollection->getUrlKeys());
    }
}
