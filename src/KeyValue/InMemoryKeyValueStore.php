<?php

namespace Brera\PoC\KeyValue;

class InMemoryKeyValueStore implements KeyValueStore
{
    /**
     * @var mixed[]
     */
    private $store = [];

    /**
     * @param string $key
     * @return mixed
     * @throws KeyNotFoundException
     */
    public function get($key)
    {
        if (!isset($this->store[$key])) {
            throw new KeyNotFoundException(sprintf('Key not found "%s"', $key));
        }
        return $this->store[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return null
     */
    public function set($key, $value)
    {
        $this->store[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->store);
    }

	/**
	 * @param array $keys
	 * @return mixed
	 */
	public function multiGet(array $keys)
	{
		$foundValues = [];

		foreach ($keys as $key) {
			if (array_key_exists($key, $this->store)) {
				$foundValues[] = $this->store[$key];
			}
		}

		return $foundValues;
	}

	/**
	 * @param array $items
	 * @return null
	 */
	public function multiSet(array $items)
	{
		$this->store = array_merge($this->store, $items);
	}
} 
