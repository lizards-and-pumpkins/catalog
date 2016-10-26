<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContext;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Util\Storage\Clearable;

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
        every($snippets, function ($snippet) {
            $this->writeSnippet($snippet);
        });
    }

    private function writeSnippet(Snippet $snippet)
    {
        $this->keyValueStore->set($snippet->getKey(), $snippet->getContent());
    }

    public function writeSearchDocument(SearchDocument $searchDocument)
    {
        $this->searchEngine->addDocument($searchDocument);
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
        array_map(function (UrlKeyForContext $urlKeyForContext) {
            $version = (string) $urlKeyForContext->getContextValue(DataVersion::CONTEXT_CODE);
            $urlKey = (string) $urlKeyForContext->getUrlKey();
            $context = (string) $urlKeyForContext;
            $urlKeyType = $urlKeyForContext->getType();
            $this->urlKeyStorage->addUrlKeyForVersion($version, $urlKey, $context, $urlKeyType);
        }, $urlKeysForContextsCollection->getUrlKeys());
    }
}
