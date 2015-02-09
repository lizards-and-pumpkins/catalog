<?php

namespace Brera;

class SnippetResultList implements \Countable, \IteratorAggregate
{
    private $snippets = [];

    public function add(SnippetResult $snippet)
    {
        $this->snippets[] = $snippet;
    }

    public function count()
    {
        return count($this->snippets);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->snippets);
    }

    public function merge(SnippetResultList $other)
    {
        $this->snippets = array_merge(
            $this->snippets,
            $other->getIterator()->getArrayCopy()
        );
    }
}
