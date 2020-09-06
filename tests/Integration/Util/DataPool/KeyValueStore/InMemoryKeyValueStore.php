<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Util\Storage\Clearable;

class InMemoryKeyValueStore implements KeyValueStore, Clearable
{
    /**
     * @var mixed[]
     */
    private $store = [];

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (!isset($this->store[$key])) {
            throw new KeyNotFoundException(sprintf('Key not found "%s"', $key));
        }
        return $this->store[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->store[$key] = $value;
    }

    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->store);
    }

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(string ...$keys) : array
    {
        return array_reduce($keys, function ($carry, string $key) {
            if (!isset($this->store[$key])) {
                return $carry;
            }
            return array_merge($carry, [$key => $this->store[$key]]);
        }, []);
    }

    /**
     * @param mixed[] $items
     */
    public function multiSet(array $items): void
    {
        $this->store = array_merge($this->store, $items);
    }

    public function clear(): void
    {
        $this->store = [];
    }
}
