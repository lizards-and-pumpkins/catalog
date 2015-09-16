<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

interface UrlKeyStore
{
    /**
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     * @return void
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString);

    /**
     * @param string $dataVersionString
     * @return array[]
     */
    public function getForDataVersion($dataVersionString);
}
