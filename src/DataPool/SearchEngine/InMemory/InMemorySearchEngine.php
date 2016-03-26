<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\InMemory;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract;
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

    /**
     * @param string[] $searchableFields
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FacetFieldTransformationRegistry $facetFieldTransformationRegistry
     */
    public function __construct(
        array $searchableFields,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) {
        $this->searchableFields = $searchableFields;
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

    /**
     * @return string[]
     */
    final protected function getSearchableFields()
    {
        return $this->searchableFields;
    }
}
