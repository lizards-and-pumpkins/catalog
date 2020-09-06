<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;

class IntegrationTestUrlKeyStoreAbstract
{
    final protected function validateDataVersionString(string $dataVersionString): void
    {
        if ('' === $dataVersionString) {
            $message = 'Invalid data version: version strings have to be one or more characters long';
            throw new DataVersionToWriteIsEmptyStringException($message);
        }
    }
}
