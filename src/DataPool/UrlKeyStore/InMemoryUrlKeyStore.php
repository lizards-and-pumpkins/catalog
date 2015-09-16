<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\Clearable;

class InMemoryUrlKeyStore extends IntegrationTestUrlKeyStoreAbstract implements UrlKeyStore, Clearable
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
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $this->validateContextDataString($contextDataString);
        $this->urlKeys[$dataVersionString][] = [$urlKeyString, $contextDataString];
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion($dataVersionString)
    {
        $this->validateDataVersionString($dataVersionString);
        return isset($this->urlKeys[$dataVersionString]) ?
            $this->urlKeys[$dataVersionString] :
            [];
    }
}
