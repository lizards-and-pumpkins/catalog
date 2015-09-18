<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

interface UrlKeyStore
{
    /**
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     * @param string $urlKeyTypeString
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString);

    /**
     * @param string $dataVersionString
     * @return array[]
     */
    public function getForDataVersion($dataVersionString);
}
