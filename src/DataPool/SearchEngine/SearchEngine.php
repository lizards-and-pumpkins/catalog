<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

interface SearchEngine
{
    /**
     * @param SearchDocument $searchDocument
     * @return void
     */
    public function addSearchDocument(SearchDocument $searchDocument);

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection);

    /**
     * @param string $queryString
     * @param Context $context
     * @return SearchDocumentCollection
     */
    public function query($queryString, Context $context);

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return SearchDocumentCollection
     */
    public function getSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context);
}
