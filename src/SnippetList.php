<?php

namespace LizardsAndPumpkins;

class SnippetList implements \Countable, \IteratorAggregate
{
    private $snippets;

    public function __construct(Snippet ...$snippets)
    {
        $this->snippets = $snippets;
    }
    
    /**
     * @return int
     */
    public function count()
    {
        return count($this->snippets);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->snippets);
    }

    public function merge(SnippetList $other)
    {
        $this->snippets = array_merge($this->snippets, iterator_to_array($other));
    }
}
