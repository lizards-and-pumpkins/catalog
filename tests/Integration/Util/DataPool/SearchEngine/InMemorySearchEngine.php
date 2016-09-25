<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class InMemorySearchEngine extends IntegrationTestSearchEngineAbstract
{
    /**
     * @var SearchDocument[]
     */
    private $index = [];

    /**
     * @var string[]
     */
    private $searchableFields;

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
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry,
        string ...$searchableFields
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->facetFieldTransformationRegistry = $facetFieldTransformationRegistry;
        $this->searchableFields = $searchableFields;
    }

    public function addDocument(SearchDocument $searchDocument)
    {
        $this->index[$this->getSearchDocumentIdentifier($searchDocument)] = $searchDocument;
    }

    /**
     * @return SearchDocument[]
     */
    final protected function getSearchDocuments() : array
    {
        return $this->index;
    }

    public function clear()
    {
        $this->index = [];
    }

    final protected function getSearchCriteriaBuilder() : SearchCriteriaBuilder
    {
        return $this->searchCriteriaBuilder;
    }

    final protected function getFacetFieldTransformationRegistry() : FacetFieldTransformationRegistry
    {
        return $this->facetFieldTransformationRegistry;
    }

    /**
     * @return string[]
     */
    final protected function getSearchableFields() : array
    {
        return $this->searchableFields;
    }
}
