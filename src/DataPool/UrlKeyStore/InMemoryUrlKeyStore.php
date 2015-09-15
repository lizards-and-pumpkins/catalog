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
     * @param string $urlKeyString
     * @param string $dataVersionString
     * @return void
     */
    public function addUrlKeyForVersion($urlKeyString, $dataVersionString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $this->urlKeys[$dataVersionString][] = $urlKeyString;
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
