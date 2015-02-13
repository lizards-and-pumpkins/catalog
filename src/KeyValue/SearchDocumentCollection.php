<?php

namespace Brera\KeyValue;

class SearchDocumentCollection
{
    /**
     * @var SearchDocument[]
     */
    private $documents = [];

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
