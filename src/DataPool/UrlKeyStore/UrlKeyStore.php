<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

interface UrlKeyStore
{
    /**
     * @param string $urlKey
     * @param string $version
     * @return void
     */
    public function addUrlKeyForVersion($urlKey, $version);
}
