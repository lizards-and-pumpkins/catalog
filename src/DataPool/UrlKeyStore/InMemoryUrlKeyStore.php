<?php

declare(strict_types=1);

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

    public function addUrlKeyForVersion(
        string $dataVersionString,
        string $urlKeyString,
        string $contextDataString,
        string $urlKeyTypeString
    ) {
        $this->validateParameters($dataVersionString, $urlKeyString);
        $this->urlKeys[$dataVersionString][] = [$urlKeyString, $contextDataString, $urlKeyTypeString];
    }

    private function validateParameters(string $dataVersionString, string $urlKeyString)
    {
        $this->validateUrlKeyString($urlKeyString);
        $this->validateDataVersionString($dataVersionString);
    }

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion(string $dataVersionString) : array
    {
        $this->validateDataVersionString($dataVersionString);
        return $this->urlKeys[$dataVersionString] ?? [];
    }
}
