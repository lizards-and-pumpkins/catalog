<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

class UrlKeyForContextCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var UrlKeyForContext[]
     */
    private $urlKeysForContext;

    public function __construct(UrlKeyForContext ...$urlKeys)
    {
        $this->urlKeysForContext = $urlKeys;
    }

    public function count() : int
    {
        return count($this->urlKeysForContext);
    }

    public function getIterator() : \Iterator
    {
        return new \ArrayIterator($this->urlKeysForContext);
    }

    /**
     * @return UrlKeyForContext[]
     */
    public function getUrlKeys() : array
    {
        return $this->urlKeysForContext;
    }
}
