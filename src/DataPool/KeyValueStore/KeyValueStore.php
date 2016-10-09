<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

interface KeyValueStore
{
    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    public function has(string $key) : bool;

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(string ...$keys) : array;

    /**
     * @param mixed[] $items
     * @return void
     */
    public function multiSet(array $items);
}
