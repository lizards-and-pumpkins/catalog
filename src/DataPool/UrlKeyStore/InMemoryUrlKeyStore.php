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

    public function clear(): void
    {
        $this->urlKeys = [];
    }

    public function addUrlKeyForVersion(
        string $dataVersionString,
        string $urlKeyString,
        string $contextDataString,
        string $urlKeyTypeString
    ) {
        $this->validateParameters($dataVersionString);
        $this->urlKeys[$dataVersionString][] = [$urlKeyString, $contextDataString, $urlKeyTypeString];
    }

    private function validateParameters(string $dataVersionString): void
    {
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
