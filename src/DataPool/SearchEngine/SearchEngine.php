<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

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
     * @return string[]
     */
    public function query($queryString, Context $context);

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return string[]
     */
    public function getContentOfSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context);
}
