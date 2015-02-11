<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\SnippetResult;
use Brera\SnippetResultList;

class DataPoolWriter
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @param KeyValueStore $keyValueStore
     */
    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    /**
     * @param SnippetResultList $snippetResultList
     */
    public function writeSnippetResultList(SnippetResultList $snippetResultList)
    {
        /** @var SnippetResult $snippetResult */
        foreach ($snippetResultList as $snippetResult) {
            $this->keyValueStore->set($snippetResult->getKey(), $snippetResult->getContent());
        }
    }
}
