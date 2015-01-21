<?php

namespace Brera\KeyValue;

use Brera\Http\HttpUrl;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;

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
        $this->checkForValidKey($key);
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
     * @param $key
     * @return array
     */
    public function getSnippetList($key)
    {
        $this->checkForValidKey($key);
        $json = $this->keyValueStore->get($key);
        $this->checkForValidJson($key, $json);
        $list = $this->jsonDecode($key, $json);

        return $list;
    }

    /**
     * @param $keys
     * @return array
     */
    public function getSnippets($keys)
    {
        if (!is_array($keys)) {
            throw new \RuntimeException(
                sprintf('multiGet needs an array to operated on, your keys is of type %s.', gettype($keys))
            );
        }
        foreach ($keys as $key) {
            $this->checkForValidKey($key);
        }

        return $this->keyValueStore->multiGet($keys);
    }

    /**
     * @param $key
     */
    private function checkForValidKey($key)
    {
        if (!is_string($key)) {
            throw new \RuntimeException('Key is not of type string.');
        }
    }

    /**
     * @param $key
     * @param $json
     */
    private function checkForValidJson($key, $json)
    {
        if (!is_string($json)) {
            throw new \RuntimeException(sprintf('List for key "%s" is no valid JSON - it is not even a string.', $key));
        }
    }

    /**
     * @param $key
     * @param $json
     * @return array
     */
    private function jsonDecode($key, $json)
    {
        $list = json_decode($json, true);

        if ($list === false) {
            $list = [];
        }
        if (!is_array($list) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('List for key "%s" is no valid JSON.', $key));
        }

        return $list;
    }

}
