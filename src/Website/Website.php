<?php

namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\Website\Exception\InvalidWebsiteCodeException;

class Website
{
    /**
     * @var string
     */
    private $websiteCode;

    /**
     * @param string $websiteCode
     */
    private function __construct($websiteCode)
    {
        $this->websiteCode = $websiteCode;
    }

    /**
     * @param string $websiteCode
     * @return Website
     */
    public static function fromString($websiteCode)
    {
        if ($websiteCode instanceof self) {
            return $websiteCode;
        }
        if (! is_string($websiteCode)) {
            $message = sprintf('The website code must be a string, got "%s"', self::getType($websiteCode));
            throw new InvalidWebsiteCodeException($message);
        }
        $trimmedWebsiteCode = trim($websiteCode);
        if ('' === $trimmedWebsiteCode) {
            throw new InvalidWebsiteCodeException('The website code may not be empty');
        }
        return new Website($trimmedWebsiteCode);
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->websiteCode;
    }

    /**
     * @param Website $otherWebsite
     * @return bool
     */
    public function isEqual(Website $otherWebsite)
    {
        return $this->websiteCode === $otherWebsite->websiteCode;
    }
}
