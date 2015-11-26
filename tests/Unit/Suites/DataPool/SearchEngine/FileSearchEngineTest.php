<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\SearchEngineNotAvailableException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\Utils\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLike
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldValue
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Utils\LocalFileSystem
 */
class FileSearchEngineTest extends AbstractSearchEngineTest
{
    /**
     * @var string
     */
    private $temporaryStorage;

    /**
     * {@inheritdoc}
     */
    final protected function createSearchEngineInstance(
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) {
        $this->prepareTemporaryStorage();

        $searchCriteriaBuilder = new SearchCriteriaBuilder($facetFieldTransformationRegistry);

        return FileSearchEngine::create(
            $this->temporaryStorage,
            $searchCriteriaBuilder,
            $facetFieldTransformationRegistry
        );
    }

    private function prepareTemporaryStorage()
    {
        $this->temporaryStorage = sys_get_temp_dir() . '/lizards-and-pumpkins-search-engine-storage';

        if (file_exists($this->temporaryStorage)) {
            $localFilesystem = new LocalFilesystem();
            $localFilesystem->removeDirectoryAndItsContent($this->temporaryStorage);
        }

        mkdir($this->temporaryStorage);
    }

    protected function tearDown()
    {
        (new LocalFilesystem())->removeDirectoryAndItsContent($this->temporaryStorage);
    }

    public function testExceptionIsThrownIfSearchEngineStorageDirIsNotWritable()
    {
        $this->setExpectedException(SearchEngineNotAvailableException::class);

        $stubFacetFieldTransformationRegistry = $this->getMock(FacetFieldTransformationRegistry::class);
        $searchCriteriaBuilder = new SearchCriteriaBuilder($stubFacetFieldTransformationRegistry);

        FileSearchEngine::create(
            'non-existing-path',
            $searchCriteriaBuilder,
            $stubFacetFieldTransformationRegistry
        );
    }
}
