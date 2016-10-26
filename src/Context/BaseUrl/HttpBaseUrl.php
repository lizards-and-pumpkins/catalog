<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\BaseUrl;

use LizardsAndPumpkins\Context\BaseUrl\Exception\InvalidBaseUrlSourceDataException;

class HttpBaseUrl implements BaseUrl
{
    /**
     * @var string
     */
    private $baseUrlString;

    public function __construct(string $baseUrlString)
    {
        $this->validateInputString($baseUrlString);
        $this->baseUrlString = $baseUrlString;
    }

    private function validateInputString(string $baseUrlString)
    {
        if (trim($baseUrlString) === '') {
            throw self::createException('Invalid empty source data for the base URL specified');
        }
        if (! preg_match('#^(?:https?:)?//#i', $baseUrlString)) {
            throw self::createException('The base URL input string contains an invalid protocol');
        }
        if (substr($baseUrlString, -1) !== '/') {
            throw self::createException('The base URL input string does not end with the required trailing slash');
        }
        if (! preg_match('#^(?:https?:)?//[a-z0-9.-]+/#i', $baseUrlString)) {
            throw self::createException(sprintf('The base URL "%s" is invalid', $baseUrlString));
        }
    }

    private static function createException(string $message) : InvalidBaseUrlSourceDataException
    {
        return new InvalidBaseUrlSourceDataException($message);
    }

    public function __toString() : string
    {
        return $this->baseUrlString;
    }
}
