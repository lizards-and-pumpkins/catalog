<?php

namespace Brera\PoC\KeyValue;

use Brera\Poc\Product\ProductId,
    Brera\PoC\Http\HttpUrl;

class DataPoolReader
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyValueStoreKeyGenerator;

    function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyValueStoreKeyGenerator = $keyValueStoreKeyGenerator;
    }

    public function getPoCProductHtml(ProductId $productId)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductHtmlKey($productId);
        return $this->keyValueStore->get($key);
    }

    /**
     * @param HttpUrl $url
     * @return mixed
     */
    public function getProductIdBySeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPocProductSeoUrlToIdKey($url);
        return $this->keyValueStore->get($key);
    }
    
    public function hasProductSeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPocProductSeoUrlToIdKey($url);
        return $this->keyValueStore->has($key);
    }
} 
