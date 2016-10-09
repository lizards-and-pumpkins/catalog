<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

class UrlKey
{
    private $urlKey;

    private function __construct(string $urlKey)
    {
        $this->urlKey = $urlKey;
    }

    public static function fromString(string $urlKey) : UrlKey
    {
        $normalizedUrlKey = self::normalizeUrlKey($urlKey);

        return new self($normalizedUrlKey);
    }

    public function __toString() : string
    {
        return (string) $this->urlKey;
    }

    private static function normalizeUrlKey(string $urlKey) : string
    {
        return preg_replace('/[^a-z0-9$\-_.+!*\'(),\/]/i', '_', $urlKey);
    }
}
