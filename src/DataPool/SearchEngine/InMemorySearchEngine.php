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
     * @param Context $queryContext
     * @return string[]
     */
    public function query($queryString, Context $queryContext)
    {
        $results = [];

        /** @var SearchDocument $searchDocument */
        foreach ($this->index as $searchDocument) {
            if (!$this->hasMatchingContext($queryContext, $searchDocument)) {
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

    /**
     * @param Context $queryContext
     * @param SearchDocument $searchDocument
     * @return bool
     */
    private function hasMatchingContext(Context $queryContext, SearchDocument $searchDocument)
    {
        foreach ($queryContext->getSupportedCodes() as $code) {
            $documentContext = $searchDocument->getContext();
            if ($documentContext->supportsCode($code)) {
                if (!$this->hasMatchingContextValue($queryContext, $documentContext, $code)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param Context $queryContext
     * @param Context $documentContext
     * @param string $code
     * @return bool
     */
    private function hasMatchingContextValue(Context $queryContext, Context $documentContext, $code)
    {
        return $queryContext->getValue($code) === $documentContext->getValue($code);
    }
}
