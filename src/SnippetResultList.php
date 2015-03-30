<?php

namespace Brera;

class SnippetResultList implements \Countable, \IteratorAggregate
{
    private $snippets = [];

    public function clear()
    {
        $this->snippets = [];
    }

    /**
     * @param SnippetResult $snippet
     */
    public function add(SnippetResult $snippet)
    {
        $this->snippets[] = $snippet;
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

    /**
     * @param SnippetResultList $other
     */
    public function merge(SnippetResultList $other)
    {
        $this->snippets = array_merge(
            $this->snippets,
            $other->getIterator()->getArrayCopy()
        );
    }
}
