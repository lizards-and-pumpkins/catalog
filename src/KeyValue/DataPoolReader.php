<?php

namespace Brera\KeyValue;

use Brera\Http\HttpUrl;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;
use RuntimeException;

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
     * @param string $key
     * @return mixed
     */
    public function getSnippet($key)
    {
        return $this->keyValueStore->get($key);
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
     * @return ProductId
     */
    public function getProductIdBySeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductSeoUrlToIdKey($url);
        $skuString = $this->keyValueStore->get($key);
        $sku = PoCSku::fromString($skuString);

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

    /**
     * This method CAN check for
     *
     * @param $key
     * @return array|mixed
     */
    public function getSnippetList($key)
    {
        if (!is_string($key)) {
            throw new RuntimeException('Key is not of type string.');
        }
        $json = $this->keyValueStore->get($key);
        if (!is_string($json)) {
            throw new RuntimeException(sprintf('List for key "%s" is no valid JSON - it is not even a string.', $key));
        }
        $list = json_decode($json, true);

        if ($list === false) {
            $list = [];
        }
        if (!is_array($list) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('List for key "%s" is no valid JSON.', $key));
        }

        return $list;
    }
}
