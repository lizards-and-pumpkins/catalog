<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

interface SearchEngine
{
    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return void
     */
    public function addSearchDocumentCollection(SearchDocumentCollection $searchDocumentCollection);

    /**
     * @param string $queryString
     * @param Context $context
     * @param string[] $facetFields
     * @return SearchEngineResponse
     */
    public function query($queryString, Context $context, array $facetFields);

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @param string[] $facetFields
     * @return SearchEngineResponse
     */
    public function getSearchDocumentsMatchingCriteria(SearchCriteria $criteria, Context $context, array $facetFields);
}
