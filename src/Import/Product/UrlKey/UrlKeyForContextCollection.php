<?php

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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->urlKeysForContext);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->urlKeysForContext);
    }

    /**
     * @return UrlKeyForContext[]
     */
    public function getUrlKeys()
    {
        return $this->urlKeysForContext;
    }
}
