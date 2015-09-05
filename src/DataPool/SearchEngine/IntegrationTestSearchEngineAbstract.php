<?php


namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    final public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        foreach ($searchDocumentCollection as $searchDocument) {
            $this->addSearchDocument($searchDocument);
        }
    }

    /**
     * @param string $queryString
     * @param Context $queryContext
     * @return SearchDocumentCollection
     */
    final public function query($queryString, Context $queryContext)
    {
        $collection = new SearchDocumentCollection;
        $searchDocuments = $this->getSearchDocuments();

        foreach ($searchDocuments as $searchDocument) {
            if (!$this->hasMatchingContext($queryContext, $searchDocument)) {
                continue;
            }

            if ($this->isAnyFieldValueOfSearchDocumentMatchesQueryString($searchDocument, $queryString)) {
                $collection->add($searchDocument);
            }
        }

        return $collection;
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return SearchDocumentCollection
     */
    final public function getSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context)
    {
        $collection = new SearchDocumentCollection;
        $searchDocuments = $this->getSearchDocuments();

        foreach ($searchDocuments as $searchDocument) {
            if ($criteria->matches($searchDocument) && $context->isSubsetOf($searchDocument->getContext())) {
                $collection->add($searchDocument);
            }
        }
        
        return $collection;
    }

    /**
     * @param SearchDocument $searchDocument
     * @return string
     */
    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument)
    {
        return $searchDocument->getProductId() . ':' . $searchDocument->getContext()->getId();
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
     * @param SearchDocument $searchDocument
     * @param string $queryString
     * @return bool
     */
    private function isAnyFieldValueOfSearchDocumentMatchesQueryString(SearchDocument $searchDocument, $queryString)
    {
        /** @var SearchDocumentField $field */
        foreach ($searchDocument->getFieldsCollection() as $field) {
            if (false !== stripos($field->getValue(), $queryString)) {
                return true;
            }
        }
        return false;
    }
}
