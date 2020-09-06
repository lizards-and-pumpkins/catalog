<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubKeyValueStore implements KeyValueStore, Clearable
{
    public function clear(): void
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        // Intentionally left empty
    }

    /**
     * @param string $key
     * @return void
     */
    public function get(string $key): void
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
    public function multiSet(array $items): void
    {
        // Intentionally left empty
    }
}
