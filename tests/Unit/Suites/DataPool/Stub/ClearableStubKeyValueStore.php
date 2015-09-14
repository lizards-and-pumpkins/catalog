<?php


namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\Utils\Clearable;

class ClearableStubKeyValueStore implements KeyValueStore, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        // Intentionally left empty
    }

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(array $keys)
    {
        // Intentionally left empty
    }

    /**
     * @param mixed[] $items
     * @return void
     */
    public function multiSet(array $items)
    {
        // Intentionally left empty
    }
}
