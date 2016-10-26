<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\Util\Storage\Clearable;

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
    public function set(string $key, $value)
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @return void
     */
    public function get(string $key)
    {
        // Intentionally left empty
    }

    public function has(string $key) : bool
    {
        // Intentionally left empty
    }

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(string ...$keys) : array
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
