<?php

namespace Brera\PoC\KeyValue;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Http\HttpUrl;
use Brera\PoC\SnippetResult;
use Brera\PoC\SnippetResultList;

class DataPoolWriter
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyGenerator;

    /**
     * @param KeyValueStore $keyValueStore
     * @param KeyValueStoreKeyGenerator $keyGenerator
     */
    public function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyGenerator = $keyGenerator;
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

    /**
     * @param ProductId $productId
     * @param string $html
     */
    public function setPoCProductHtml(ProductId $productId, $html)
    {
        $this->keyValueStore->set(
            $this->keyGenerator->createPoCProductHtmlKey($productId),
            $html
        );
    }

    /**
     * @param ProductId $productId
     * @param HttpUrl $seoUrl
     */
    public function setProductIdBySeoUrl(ProductId $productId, HttpUrl $seoUrl)
    {
        $this->keyValueStore->set(
            $this->keyGenerator->createPoCProductSeoUrlToIdKey($seoUrl),
            $productId
        );
    }
} 
