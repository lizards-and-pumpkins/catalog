<?php

namespace Brera\PoC\KeyValue;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Http\HttpUrl;
use Brera\PoC\Product\PoCSku;

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

    /**
     * @param KeyValueStore $keyValueStore
     * @param KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator
     */
    function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyValueStoreKeyGenerator = $keyValueStoreKeyGenerator;
    }

    /**
     * @param ProductId $productId
     * @return mixed
     */
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
        $key = $this->keyValueStoreKeyGenerator->createPoCProductSeoUrlToIdKey($url);
        $skuString = $this->keyValueStore->get($key);
	    $sku = new PoCSku($skuString);

	    return ProductId::fromSku($sku);
    }

    /**
     * @param HttpUrl $url
     * @return bool
     */
    public function hasProductSeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductSeoUrlToIdKey($url);
        return $this->keyValueStore->has($key);
    }
} 
