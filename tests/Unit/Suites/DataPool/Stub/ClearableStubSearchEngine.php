<?php


namespace LizardsAndPumpkins\DataPool\Stub;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Utils\Clearable;

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
