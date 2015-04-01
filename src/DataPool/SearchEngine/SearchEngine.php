<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

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
     * @param string[] $queryCriteria
     * @param Context $context
     * @return \string[]
     */
    public function queryGivenFields(array $queryCriteria, Context $context);
}
