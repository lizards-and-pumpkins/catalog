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
     * @param string $versionString
     * @param string $urlKeyString
     * @param string $contextString
     */
    public function addUrlKeyForVersion($versionString, $urlKeyString, $contextString)
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
