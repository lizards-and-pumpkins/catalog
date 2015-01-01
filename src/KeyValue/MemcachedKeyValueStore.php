<?php

namespace Brera\PoC\KeyValue;

class MemcachedKeyValueStore implements KeyValueStore
{
	const MEMCACHED_RES_NOTFOUND = 16;

	/**
	 * @var \Memcached
	 */
	private $client;

	public function __construct(\Memcached $client)
	{
		$this->client = $client;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return null
	 */
	public function set($key, $value)
	{
		$this->client->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (!$value = $this->client->get($key)) {
			throw new KeyNotFoundException(sprintf('Key not found "%s"', $key));
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		$this->client->get($key);

		return self::MEMCACHED_RES_NOTFOUND !== $this->client->getResultCode();
	}

	/**
	 * @param array $keys
	 * @return mixed
	 */
	public function multiGet(array $keys)
	{
		return $this->client->getMulti($keys);
	}

	/**
	 * @param array $items
	 * @return null
	 */
	public function multiSet(array $items)
	{
		$this->client->setMulti($items);
	}
} 
