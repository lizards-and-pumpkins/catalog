<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

interface UrlKeyStore
{
    public function addUrlKeyForVersion(
        string $dataVersionString,
        string $urlKeyString,
        string $contextDataString,
        string $urlKeyTypeString
    );

    /**
     * @param string $dataVersionString
     * @return string[]
     */
    public function getForDataVersion(string $dataVersionString) : array;
}
