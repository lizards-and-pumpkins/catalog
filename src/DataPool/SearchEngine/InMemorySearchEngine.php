<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FacetFieldTransformationRegistry
     */
    private $facetFieldTransformationRegistry;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->facetFieldTransformationRegistry = $facetFieldTransformationRegistry;
    }

    public function addDocument(SearchDocument $searchDocument)
    {
        $this->index[$this->getSearchDocumentIdentifier($searchDocument)] = $searchDocument;
    }

    /**
     * @return SearchDocument[]
     */
    final protected function getSearchDocuments()
    {
        return $this->index;
    }

    public function clear()
    {
        $this->index = [];
    }

    /**
     * @return SearchCriteriaBuilder
     */
    final protected function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    final protected function getFacetFieldTransformationRegistry()
    {
        return $this->facetFieldTransformationRegistry;
    }
}
