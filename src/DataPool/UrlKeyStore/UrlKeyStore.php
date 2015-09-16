<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

interface UrlKeyStore
{
    /**
     * @param string $urlKeyString
     * @param string $dataVersionString
     * @return void
     */
    public function addUrlKeyForVersion($urlKeyString, $dataVersionString);

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion($dataVersionString);
}
