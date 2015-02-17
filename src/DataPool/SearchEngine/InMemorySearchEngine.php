<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Environment\Environment;

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
     * @param Environment $environment
     * @return mixed[]
     */
    public function query($queryString, Environment $environment)
    {
        $results = [];

        /** @var SearchDocument $searchDocument */
        foreach ($this->index as $searchDocument) {
            if ($environment != $searchDocument->getEnvironment()) {
                continue;
            }

            $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();

            foreach ($searchDocumentFieldsCollection->getFields() as $field) {
                if (false !== stripos($field->getValue(), $queryString)) {
                    array_push($results, $searchDocument->getContent());
                }
            }
        }

        return $results;
    }
}
