<?php

namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Util\Storage\Clearable;

class ClearableStubUrlKeyStore implements UrlKeyStore, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    /**
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     * @param string $urlKeyTypeString
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString)
    {
        // Intentionally left empty
    }

    /**
     * @param string $dataVersionString
     * @return array[]
     */
    public function getForDataVersion($dataVersionString)
    {
        // Intentionally left empty
    }
}
