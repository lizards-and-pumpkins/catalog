<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\Clearable;

class InMemoryUrlKeyStore implements UrlKeyStore, Clearable
{
    /**
     * @var string[]
     */
    private $urlKeys = [];

    public function clear()
    {
        $this->urlKeys = [];
    }

    /**
     * @param string $urlKey
     * @param string $version
     * @return void
     */
    public function addUrlKeyForVersion($urlKey, $version)
    {
        $this->urlKeys[$version][] = $urlKey;
    }

    /**
     * @param string $version
     * @return string[]
     */
    public function getForVersion($version)
    {
        return isset($this->urlKeys[$version]) ?
            $this->urlKeys[$version] :
            [];
    }
}
