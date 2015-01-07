<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\Product\PoCSku;

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
} 
