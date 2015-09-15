<?php


namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Utils\Clearable;

class ClearableStubUrlKeyStore implements UrlKeyStore, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    /**
     * @param string $urlKey
     * @param string $version
     */
    public function addUrlKeyForVersion($urlKey, $version)
    {
        // Intentionally left empty
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion($dataVersionString)
    {
        // Intentionally left empty
    }
}
