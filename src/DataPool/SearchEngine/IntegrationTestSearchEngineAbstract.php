<?php


namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    final public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
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
    final public function query($queryString, Context $queryContext)
    {
        $results = [];
        $searchDocuments = $this->getSearchDocuments();

        foreach ($searchDocuments as $searchDocument) {
            if (!$this->hasMatchingContext($queryContext, $searchDocument)) {
                continue;
            }

            $results = $this->findDocumentsMatchingAnyFields($queryString, $searchDocument, $results);
        }

        return $results;
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return string[]
     */
    final public function getContentOfSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context)
    {
        $result = [];
        
        foreach ($this->getSearchDocuments() as $searchDocument) {
            // todo: check for matching context
            if ($searchDocument->isMatchingCriteria($criteria)) {
                $content = $searchDocument->getContent();
                if (! in_array($content, $result)) {
                    $result[] = $content;
                }
            }
        }
        
        return $result;
    }

    /**
     * @param Context $queryContext
     * @param SearchDocument $searchDocument
     * @return bool
     */
    private function hasMatchingContext(Context $queryContext, SearchDocument $searchDocument)
    {
        $hasAtLeastOneMatchingContextPart = false;
        $documentContext = $searchDocument->getContext();
        foreach ($queryContext->getSupportedCodes() as $code) {
            if ($documentContext->supportsCode($code)) {
                if (!$this->hasMatchingContextValue($queryContext, $documentContext, $code)) {
                    return false;
                }
                $hasAtLeastOneMatchingContextPart = true;
            }
        }
        return $hasAtLeastOneMatchingContextPart;
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

    /**
     * @param string $queryString
     * @param SearchDocument $searchDocument
     * @param string[] $results
     * @return string[]
     */
    private function findDocumentsMatchingAnyFields($queryString, SearchDocument $searchDocument, array $results)
    {
        $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();
        $content = $searchDocument->getContent();
        foreach ($searchDocumentFieldsCollection->getFields() as $field) {
            if (! in_array($content, $results) && false !== stripos($field->getValue(), $queryString)) {
                $results[] = $searchDocument->getContent();
            }
        }
        return $results;
    }
}
