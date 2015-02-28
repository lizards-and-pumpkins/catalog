<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

class InMemorySearchEngine implements SearchEngine
{
    /**
     * @var mixed[]
     */
    private $index = [];

    /**
     * @param SearchDocument $searchDocument
     */
    public function addSearchDocument(SearchDocument $searchDocument)
    {
        array_push($this->index, $searchDocument);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        foreach ($searchDocumentCollection->getDocuments() as $searchDocument) {
            $this->addSearchDocument($searchDocument);
        }
    }

    /**
     * @param string $queryString
     * @param Context $context
     * @return string[]
     */
    public function query($queryString, Context $context)
    {
        $results = [];

        /** @var SearchDocument $searchDocument */
        foreach ($this->index as $searchDocument) {
            if ($context != $searchDocument->getContext()) {
                continue;
            }

            $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();

            foreach ($searchDocumentFieldsCollection->getFields() as $field) {
                if (!in_array($searchDocument->getContent(), $results)) {
                    if (false !== stripos($field->getValue(), $queryString)) {
                        array_push($results, $searchDocument->getContent());
                    }
                }
            }
        }

        return array_unique($results);
    }
}
