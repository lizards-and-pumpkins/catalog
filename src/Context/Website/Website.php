<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteCodeException;

class Website
{
    const CONTEXT_CODE = 'website';
    
    /**
     * @var string
     */
    private $websiteCode;

    private function __construct(string $websiteCode)
    {
        $this->websiteCode = $websiteCode;
    }

    /**
     * @param Website|string $websiteCode
     * @return Website
     */
    public static function fromString($websiteCode) : Website
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
    private static function getType($variable) : string
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

    public function isEqual(Website $otherWebsite) : bool
    {
        return $this->websiteCode === $otherWebsite->websiteCode;
    }
}
