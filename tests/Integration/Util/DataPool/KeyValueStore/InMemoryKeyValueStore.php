<?php

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Util\Storage\Clearable;

class InMemoryKeyValueStore implements KeyValueStore, Clearable
{
    /**
     * @var array
     */
    private $store = [];

    /**
     * @param string $key
     * @return mixed
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
     * @return void
     */
    public function set($key, $value)
    {
        $this->store[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->store);
    }

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(array $keys)
    {
        $foundValues = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->store)) {
                $foundValues[$key] = $this->store[$key];
            }
        }

        return $foundValues;
    }

    /**
     * @param mixed[] $items
     * @return null
     */
    public function multiSet(array $items)
    {
        $this->store = array_merge($this->store, $items);
    }

    public function clear()
    {
        $this->store = [];
    }
}
