<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyToWriteIsEmptyStringException;

class IntegrationTestUrlKeyStoreAbstract
{
    final protected function validateUrlKeyString(string $urlKey)
    {
        if ('' === $urlKey) {
            $message = 'Invalid URL key: url key strings have to be one or more characters long';
            throw new UrlKeyToWriteIsEmptyStringException($message);
        }
    }

    final protected function validateDataVersionString(string $dataVersionString)
    {
        if ('' === $dataVersionString) {
            $message = 'Invalid data version: version strings have to be one or more characters long';
            throw new DataVersionToWriteIsEmptyStringException($message);
        }
    }
}
