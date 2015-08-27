<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

use Brera\DataPool\SearchEngine\SearchCriteria;

class SearchDocumentCollection implements \Countable
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
     * @param SearchCriteria $criteria
     * @return SearchDocumentCollection
     */
    public function getCollectionFilteredByCriteria(SearchCriteria $criteria)
    {
        $filteredCollection = new self;

        foreach ($this->documents as $searchDocument) {
            if ($searchDocument->isMatchingCriteria($criteria)) {
                $filteredCollection->add($searchDocument);
            }
        }

        return $filteredCollection;
    }
}
