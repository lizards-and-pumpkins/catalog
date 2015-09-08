<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

class SearchDocumentCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var SearchDocument[]
     */
    private $documents = [];

    public function add(SearchDocument $document)
    {
        $this->documents[] = $document;
    }

    /**
     * @return SearchDocument[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->documents);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->documents);
    }
}
