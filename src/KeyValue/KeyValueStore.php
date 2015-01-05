<?php

namespace Brera\KeyValue;

interface KeyValueStore
{
    /**
     * @param string $key
     * @param mixed $value
     * @return null
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @return bool
     */
    public function has($key);

	/**
	 * @param array $keys
	 * @return mixed
	 */
    public function multiGet(array $keys);

	/**
	 * @param array $items
	 * @return null
	 */
	public function multiSet(array $items);
}
