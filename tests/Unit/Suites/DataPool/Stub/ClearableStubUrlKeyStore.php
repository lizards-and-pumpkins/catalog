<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubUrlKeyStore implements UrlKeyStore, Clearable
{
    public function clear(): void
    {
        // Intentionally left empty
    }

    public function addUrlKeyForVersion(
        string $dataVersionString,
        string $urlKeyString,
        string $contextDataString,
        string $urlKeyTypeString
    ) {
        // Intentionally left empty
    }

    /**
     * @param string $dataVersionString
     * @return array[]
     */
    public function getForDataVersion(string $dataVersionString) : array
    {
        // Intentionally left empty
    }
}
