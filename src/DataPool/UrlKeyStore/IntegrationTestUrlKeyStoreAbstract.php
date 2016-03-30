<?php

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\ContextDataIsNotAStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionIsNotAStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\DataVersionToWriteIsEmptyStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyIsNotAStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyToWriteIsEmptyStringException;
use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\UrlKeyTypeIsNotAStringException;

class IntegrationTestUrlKeyStoreAbstract
{
    /**
     * @param string $urlKey
     */
    final protected function validateUrlKeyString($urlKey)
    {
        if (!is_string($urlKey)) {
            $variableType = $this->getVariableType($urlKey);
            $message = sprintf('URL keys have to be strings for storage in the UrlKeyStore, got "%s"', $variableType);
            throw new UrlKeyIsNotAStringException($message);
        }
        if ('' === $urlKey) {
            $message = 'Invalid URL key: url key strings have to be one or more characters long';
            throw new UrlKeyToWriteIsEmptyStringException($message);
        }
    }

    /**
     * @param string $dataVersionString
     */
    final protected function validateDataVersionString($dataVersionString)
    {
        if (!is_string($dataVersionString)) {
            $message = sprintf(
                'The data version has to be string for use with the UrlKeyStore, got "%s"',
                $this->getVariableType($dataVersionString)
            );
            throw new DataVersionIsNotAStringException($message);
        }
        if ('' === $dataVersionString) {
            $message = 'Invalid data version: version strings have to be one or more characters long';
            throw new DataVersionToWriteIsEmptyStringException($message);
        }
    }

    /**
     * @param string $contextDataString
     */
    final protected function validateContextDataString($contextDataString)
    {
        if (!is_string($contextDataString)) {
            $message = sprintf(
                'The context data has to be string for use with the UrlKeyStore, got "%s"',
                $this->getVariableType($contextDataString)
            );
            throw new ContextDataIsNotAStringException($message);
        }
    }

    /**
     * @param string $urlKeyTypeString
     */
    final protected function validateUrlKeyTypeString($urlKeyTypeString)
    {
        if (!is_string($urlKeyTypeString)) {
            $message = sprintf(
                'The url key type has to be string, got "%s"',
                $this->getVariableType($urlKeyTypeString)
            );
            throw new UrlKeyTypeIsNotAStringException($message);
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    final protected function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
