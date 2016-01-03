<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Projection\UrlKeyForContext;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;
use LizardsAndPumpkins\Snippet;
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

    public function writeSnippets(Snippet ...$snippets)
    {
        array_map([$this, 'writeSnippet'], $snippets);
    }

    private function writeSnippet(Snippet $snippet)
    {
        $this->keyValueStore->set($snippet->getKey(), $snippet->getContent());
    }

    public function writeSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        $this->searchEngine->addSearchDocumentCollection($searchDocumentCollection);
    }

    public function clear()
    {
        $this->clearComponent($this->searchEngine);
        $this->clearComponent($this->keyValueStore);
        $this->clearComponent($this->urlKeyStorage);
    }

    /**
     * @param object $instance
     */
    private function clearComponent($instance)
    {
        if ($instance instanceof Clearable) {
            $instance->clear();
        }
    }

    public function writeUrlKeyCollection(UrlKeyForContextCollection $urlKeysForContextsCollection)
    {
        @array_map(function (UrlKeyForContext $urlKeyForContext) {
            $version = (string) $urlKeyForContext->getContextValue(ContextVersion::CODE);
            $urlKey = (string) $urlKeyForContext->getUrlKey();
            $context = (string) $urlKeyForContext;
            $urlKeyType = $urlKeyForContext->getType();
            $this->urlKeyStorage->addUrlKeyForVersion($version, $urlKey, $context, $urlKeyType);
        }, $urlKeysForContextsCollection->getUrlKeys());
    }
}
