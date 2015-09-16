<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

class IntegrationTestUrlKeyStoreAbstract
{
    /**
     * @param mixed $urlKey
     */
    final protected function validateUrlKeyString($urlKey)
    {
        if (!is_string($urlKey)) {
            $variableType = $this->getVariableType($urlKey);
            $message = sprintf('URL keys have to be strings for storage in the UrlKeyStore, got "%s"', $variableType);
            throw new Exception\UrlKeyIsNotAStringException($message);
        }
        if ('' === $urlKey) {
            $message = 'Invalid URL key: url key strings have to be one or more characters long';
            throw new Exception\UrlKeyToWriteIsEmptyStringException($message);
        }
    }

    /**
     * @param mixed $dataVersionString
     */
    final protected function validateDataVersionString($dataVersionString)
    {
        if (!is_string($dataVersionString)) {
            $message = sprintf(
                'The data version has to be string for use with the UrlKeyStore, got "%s"',
                $this->getVariableType($dataVersionString)
            );
            throw new Exception\DataVersionIsNotAStringException($message);
        }
        if ('' === $dataVersionString) {
            $message = 'Invalid data version: version strings have to be one or more characters long';
            throw new Exception\DataVersionToWriteIsEmptyStringException($message);
        }
    }

    /**
     * @param mixed $contextDataString
     */
    final protected function validateContextDataString($contextDataString)
    {
        if (!is_string($contextDataString)) {
            $message = sprintf(
                'The context data has to be string for use with the UrlKeyStore, got "%s"',
                $this->getVariableType($contextDataString)
            );
            throw new Exception\ContextDataIsNotAStringException($message);
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
