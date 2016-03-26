<?php

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use LizardsAndPumpkins\DataPool\UrlKeyStore\Exception\InvalidUrlKeySourceException;

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

        $normalizedUrlKey = self::normalizeUrlKey($urlKey);

        return new self($normalizedUrlKey);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->urlKey;
    }

    /**
     * @param string $urlKey
     * @return string
     */
    private static function normalizeUrlKey($urlKey)
    {
        return preg_replace('/[^a-z0-9$\-_.+!*\'(),\/]/i', '_', $urlKey);
    }
}
