<?php

namespace Brera\DataPool\SearchEngine;

class SearchDocumentCollection
{
    /**
     * @var SearchDocument[]
     */
    private $documents = [];

    /**
     * @param SearchDocument $document
     */
    public function add(SearchDocument $document)
    {
        array_push($this->documents, $document);
    }

    /**
     * @return SearchDocument[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
