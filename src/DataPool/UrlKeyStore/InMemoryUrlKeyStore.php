<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Util\Storage\Clearable;

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
     * @param string $urlKeyTypeString
     */
    public function addUrlKeyForVersion($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString)
    {
        $this->validateParameters($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString);
        $this->urlKeys[$dataVersionString][] = [$urlKeyString, $contextDataString, $urlKeyTypeString];
    }

    /**
     * @param string $dataVersionString
     * @param string $urlKeyString
     * @param string $contextDataString
     * @param string $urlKeyTypeString
     */
    private function validateParameters($dataVersionString, $urlKeyString, $contextDataString, $urlKeyTypeString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
        $this->validateContextDataString($contextDataString);
        $this->validateUrlKeyTypeString($urlKeyTypeString);
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
