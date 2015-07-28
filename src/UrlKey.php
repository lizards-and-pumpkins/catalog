<?php

namespace Brera;

class UrlKey
{
    private $urlKey;

    /**
     * @param string $urlKey
     */
    private function __construct($urlKey)
    {
        $this->urlKey = $urlKey;
    }

    /**
     * @param string $urlKey
     * @return UrlKey
     */
    public static function fromString($urlKey)
    {
        if (!is_string($urlKey)) {
            throw new InvalidUrlKeySourceException(
                sprintf('URL key can be only created from string, got %s.', gettype($urlKey))
            );
        }

        return new self($urlKey);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->urlKey;
    }
}
