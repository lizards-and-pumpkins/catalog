<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\IntegrationTestSearchEngineAbstract;
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
     * @var FacetFieldTransformationRegistry
     */
    private $facetFieldTransformationRegistry;

    public function __construct(
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry,
        string ...$searchableFields
    ) {
        $this->facetFieldTransformationRegistry = $facetFieldTransformationRegistry;
        $this->searchableFields = $searchableFields;
    }

    public function addDocument(SearchDocument $searchDocument): void
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

    public function clear(): void
    {
        $this->index = [];
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
