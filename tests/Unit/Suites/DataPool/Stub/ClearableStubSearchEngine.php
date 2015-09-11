<?php


namespace Brera\DataPool\Stub;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchEngine;
use Brera\Utils\Clearable;

class ClearableStubSearchEngine implements SearchEngine, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    public function addSearchDocument(SearchDocument $searchDocument)
    {
        // Intentionally left empty
    }

    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection)
    {
        // Intentionally left empty
    }

    /**
     * @param string $queryString
     * @param Context $context
     * @return void
     */
    public function query($queryString, Context $context)
    {
        // Intentionally left empty
    }

    public function getSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context)
    {
        // Intentionally left empty
    }
}
