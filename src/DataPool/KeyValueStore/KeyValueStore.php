<?php

namespace LizardsAndPumpkins\DataPool\KeyValueStore;

interface KeyValueStore
{
    /**
     * @param string $key
     * @param mixed $value
     * @return void
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
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(array $keys);

    /**
     * @param mixed[] $items
     * @return void
     */
    public function multiSet(array $items);
}
