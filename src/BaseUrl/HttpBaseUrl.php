<?php


namespace LizardsAndPumpkins\BaseUrl;

use LizardsAndPumpkins\BaseUrl;
use LizardsAndPumpkins\BaseUrl\Exception\InvalidBaseUrlSourceDataException;

class HttpBaseUrl implements BaseUrl
{
    /**
     * @var string
     */
    private $baseUrlString;

    /**
     * @param string $baseUrlString
     */
    private function __construct($baseUrlString)
    {
        $this->validateInputString($baseUrlString);
        $this->baseUrlString = $baseUrlString;
    }

    /**
     * @param string $baseUrlString
     * @return HttpBaseUrl
     */
    public static function fromString($baseUrlString)
    {
        return new self($baseUrlString);
    }

    /**
     * @param string $variable
     * @return string
     */
    private static function getTypeAsString($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param string $baseUrlString
     */
    private function validateInputString($baseUrlString)
    {
        if (!is_string($baseUrlString)) {
            $type = self::getTypeAsString($baseUrlString);
            throw self::createException(sprintf('The input for the base URL has to be a string, got "%s"', $type));
        }
        if (empty(trim($baseUrlString))) {
            throw self::createException('Invalid empty source data for the base URL specified');
        }
        if (substr($baseUrlString, 0, 8) !== 'https://' && substr($baseUrlString, 0, 7) !== 'http://') {
            throw self::createException('The base URL input string does not contain the protocol');
        }
        if (substr($baseUrlString, -1) !== '/') {
            throw self::createException('The base URL input string does not end with the required trailing slash');
        }
        if (! preg_match('#https?://[a-z09.-]+/#i', $baseUrlString)) {
            throw self::createException(sprintf('The base URL "%s" is invalid', $baseUrlString));
        }
    }

    /**
     * @param string $message
     * @return InvalidBaseUrlSourceDataException
     */
    private static function createException($message)
    {
        return new InvalidBaseUrlSourceDataException($message);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->baseUrlString;
    }
}
