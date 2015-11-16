<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLike;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Utils\Clearable;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context
     */
    private $testContext;

    /**
     * @var SearchEngine|Clearable
     */
    private $searchEngine;

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @return SearchDocument
     */
    private function createSearchDocument(array $fields, ProductId $productId)
    {
        return $this->createSearchDocumentWithContext($fields, $productId, $this->testContext);
    }

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @param Context $context
     * @return SearchDocument
     */
    private function createSearchDocumentWithContext(array $fields, ProductId $productId, Context $context)
    {
        return new SearchDocument(SearchDocumentFieldCollection::fromArray($fields), $context, $productId);
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function assertCollectionContainsDocumentForProductId(
        SearchDocumentCollection $collection,
        ProductId $productId
    ) {
        foreach ($collection->getDocuments() as $document) {
            if ($document->getProductId() == $productId) {
                $this->assertTrue(true);
                return;
            }
        }
        $this->fail(sprintf('Failed asserting collection contains document for product ID: %s', $productId));
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function assertCollectionDoesNotContainDocumentForProductId(
        SearchDocumentCollection $collection,
        ProductId $productId
    ) {
        foreach ($collection->getDocuments() as $document) {
            if ($document->getProductId() == $productId) {
                $this->fail(
                    sprintf('Failed asserting collection does not contain document for product ID: %s', $productId)
                );
            }
        }
        $this->assertTrue(true);
    }

    /**
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataParts(array $contextDataSet)
    {
        $dataVersion = DataVersion::fromVersionString('-1');
        $contextBuilder = new ContextBuilder($dataVersion);

        return $contextBuilder->createContextsFromDataSets([$contextDataSet])[0];
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection(SearchDocument ...$searchDocuments)
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getIterator')->willReturn(new \ArrayIterator($searchDocuments));
        $stubSearchDocumentCollection->method('getDocuments')->willReturn($searchDocuments);

        return $stubSearchDocumentCollection;
    }

    /**
     * @param string $sortByFieldCode
     * @param string $sortDirection
     * @return SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSortOrderConfig($sortByFieldCode, $sortDirection)
    {
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($sortByFieldCode);

        $sortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $sortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $sortOrderConfig->method('getSelectedDirection')->willReturn($sortDirection);

        return $sortOrderConfig;
    }

    protected function setUp()
    {
        $this->searchEngine = $this->createSearchEngineInstance();
        $this->testContext = $this->createContextFromDataParts([WebsiteContextDecorator::CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testSearchEngineResponseIsReturned()
    {
        $criteria = SearchCriterionEqual::create('foo', 'bar');
        $selectedFilters = [];
        $facetFiltersConfig = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFiltersConfig,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $this->assertInstanceOf(SearchEngineResponse::class, $result);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value-1']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value-2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value-2']);

        $documentFields = [$fieldName => $fieldValue];
        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $queryContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testPartialContextsAreMatched()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['locale' => 'value2']);

        $documentFields = [$fieldName => $fieldValue];
        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $queryContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $fieldName = 'foo';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument([$fieldName => 'Hidden bar here.'], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => 'Here there is none.'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionLike::create($fieldName, 'bar');
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        $searchCriteria = SearchCriterionEqual::create('foo', 'some-value-which-is-definitely-absent-in-index');
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $this->assertCount(0, $searchEngineResponse->getSearchDocuments());
    }

    /**
     * @dataProvider searchCriteriaProvider
     * @param SearchCriteria $searchCriteria
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingGivenCriteria(SearchCriteria $searchCriteria)
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['foo' => 'baz'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    /**
     * @return array[]
     */
    public function searchCriteriaProvider()
    {
        return [
            [SearchCriterionEqual::create('foo', 'bar')],
            [SearchCriterionNotEqual::create('foo', 'baz')],
            [
                CompositeSearchCriterion::createAnd(
                    SearchCriterionEqual::create('foo', 'bar'),
                    SearchCriterionNotEqual::create('foo', 'baz')
                )
            ],
        ];
    }

    /**
     * @dataProvider searchRangeCriteriaProvider
     * @param SearchCriteria $searchCriteria
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingRangeCriteria(SearchCriteria $searchCriteria)
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['price' => 10], $productAId);
        $searchDocumentB = $this->createSearchDocument(['price' => 20], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    /**
     * @return array[]
     */
    public function searchRangeCriteriaProvider()
    {
        return [
            [SearchCriterionLessThan::create('price', 20)],
            [SearchCriterionLessOrEqualThan::create('price', 10)],
            [
                CompositeSearchCriterion::createAnd(
                    SearchCriterionGreaterThan::create('price', 5),
                    SearchCriterionLessOrEqualThan::create('price', 10)
                )
            ],
            [
                CompositeSearchCriterion::createAnd(
                    SearchCriterionGreaterOrEqualThan::create('price', 10),
                    SearchCriterionLessThan::create('price', 20)
                )
            ],
        ];
    }

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $productId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productId);
    }

    public function testItClearsTheStorage()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $this->searchEngine->clear();

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $this->assertEmpty($searchEngineResponse->getSearchDocuments());
    }

    public function testDocumentIsUniqueForProductIdAndContextCombination()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $fieldName = 'foo';
        $uniqueValue = uniqid();
        $documentFields = [$fieldName => $uniqueValue];

        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $this->testContext);
        $searchDocumentC = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection(
            $searchDocumentA,
            $searchDocumentB,
            $searchDocumentC
        );

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $uniqueValue);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCount(2, $result);
        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testFacetFieldCollectionOnlyContainsSpecifiedAttributes()
    {
        $keyword = uniqid();
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $fieldACode = 'foo';
        $fieldBCode = 'bar';
        $fieldCCode = 'baz';

        $searchDocumentA = $this->createSearchDocument([$fieldACode => $keyword, $fieldBCode => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldBCode => $keyword, $fieldCCode => 'test'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create($fieldACode, $keyword),
            SearchCriterionEqual::create($fieldBCode, $keyword),
            SearchCriterionEqual::create($fieldCCode, $keyword)
        );

        $selectedFilters = [];
        $facetFields = ['foo' => [], 'bar' => []];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $expectedFooFacetField = new SearchEngineFacetField(
            AttributeCode::fromString($fieldACode),
            SearchEngineFacetFieldValueCount::create($keyword, 1)
        );
        $expectedBarFacetField = new SearchEngineFacetField(
            AttributeCode::fromString($fieldBCode),
            SearchEngineFacetFieldValueCount::create($keyword, 2)
        );
        $result = $searchEngineResponse->getFacetFieldCollection();

        $this->assertCount(2, $result->getFacetFields());
        $this->assertContains($expectedFooFacetField, $result->getFacetFields(), '', false, false);
        $this->assertContains($expectedBarFacetField, $result->getFacetFields(), '', false, false);
    }

    public function testFacetFieldCollectionContainsConfiguredRanges()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldCode = 'price';

        $documentA = $this->createSearchDocument([$fieldCode => 1], $productAId);
        $documentB = $this->createSearchDocument([$fieldCode => 11], $productBId);
        $documentC = $this->createSearchDocument([$fieldCode => 31], $productCId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldCode, 0);
        $selectedFilters = [];
        $facetFields = [
            $fieldCode => [
                ['from' => SearchEngine::RANGE_WILDCARD, 'to' => '10'],
                ['from' => '10', 'to' => '20'],
                ['from' => '20', 'to' => '30'],
                ['from' => '30', 'to' => SearchEngine::RANGE_WILDCARD],
            ]
        ];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $expectedFacetFields = [
            new SearchEngineFacetField(
                AttributeCode::fromString($fieldCode),
                SearchEngineFacetFieldValueCount::create(
                    SearchEngine::RANGE_WILDCARD . SearchEngine::RANGE_DELIMITER . '10',
                    1
                ),
                SearchEngineFacetFieldValueCount::create('10' . SearchEngine::RANGE_DELIMITER . '20', 1),
                SearchEngineFacetFieldValueCount::create(
                    '30' . SearchEngine::RANGE_DELIMITER . SearchEngine::RANGE_WILDCARD,
                    1
                )
            )
        ];
        $facetFieldsCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->assertEquals($expectedFacetFields, $facetFieldsCollection->getFacetFields());
    }

    public function testOnlyProductsFromARequestedPageAreReturned()
    {
        $field = 'foo';
        $keyword = uniqid();

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument([$field => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument([$field => $keyword], $productBId);

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($field, $keyword);
        $selectedFilters = [];
        $pageNumber = 1;
        $rowsPerPage = 1;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            [],
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $this->assertCount(1, $searchEngineResponse->getSearchDocuments());
        $this->assertSame(2, $searchEngineResponse->getTotalNumberOfResults());
    }

    public function testSelectedFiltersAreAddedToCriteria()
    {
        $keywordA = uniqid();
        $keywordB = uniqid();

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $fieldACode = 'foo';
        $fieldBCode = 'bar';

        $documentA = $this->createSearchDocument([$fieldACode => $keywordA, $fieldBCode => $keywordB], $productAId);
        $documentB = $this->createSearchDocument([$fieldACode => $keywordA], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldACode, $keywordA);

        $selectedFilters = [$fieldBCode => [$keywordB]];
        $facetFields = [$fieldACode => [], $fieldBCode => []];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCount(1, $result);
        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    public function testSelectedFiltersOptionValueSiblingsAreIncludedIntoFilterOptionValues()
    {
        $fieldACode = 'foo';
        $fieldBCode = 'bar';

        $fieldValueA = uniqid();
        $fieldValueB = uniqid();
        $keyword = uniqid();

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $documentA = $this->createSearchDocument([$fieldACode => $fieldValueA, $fieldBCode => $keyword], $productAId);
        $documentB = $this->createSearchDocument([$fieldACode => $fieldValueB, $fieldBCode => $keyword], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldBCode, $keyword);
        $selectedFilters = [$fieldACode => [$fieldValueB]];
        $facetFields = [$fieldACode => []];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('whatever', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $result = $searchEngineResponse->getFacetFieldCollection()->getFacetFields();

        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]->getValues());
    }

    public function testReturnedDocumentsCollectionIsSortedAccordingToGivenOrder()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldCode = 'price';

        $documentA = $this->createSearchDocument([$fieldCode => 3], $productAId);
        $documentB = $this->createSearchDocument([$fieldCode => 1], $productBId);
        $documentC = $this->createSearchDocument([$fieldCode => 2], $productCId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldCode, 0);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig($fieldCode, SortOrderDirection::DESC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $expectedDocuments = [$documentA, $documentC, $documentB];

        $this->assertEquals($expectedDocuments, $searchEngineResponse->getSearchDocuments()->getDocuments());
    }

    public function testDocumentsCollectionIsNotSortedByMultivaluedAttribute()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldCode = 'foo';

        $documentA = $this->createSearchDocument([$fieldCode => ['foo', 'bar']], $productAId);
        $documentB = $this->createSearchDocument([$fieldCode => ['bar', 'baz']], $productBId);
        $documentC = $this->createSearchDocument([$fieldCode => ['baz', 'qux']], $productCId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldCode, 0);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig($fieldCode, SortOrderDirection::DESC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $unExpectedDocuments = [$documentA, $documentB, $documentC];

        $this->assertNotEquals($unExpectedDocuments, $searchEngineResponse->getSearchDocuments()->getDocuments());
    }

    public function testReturnedDocumentsCollectionIsSortedByStringValuesCaseInsensitively()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldCode = 'foo';

        $documentA = $this->createSearchDocument([$fieldCode => 'abc'], $productAId);
        $documentB = $this->createSearchDocument([$fieldCode => 'Acd'], $productBId);
        $documentC = $this->createSearchDocument([$fieldCode => 'aCa'], $productCId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldCode, 0);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig($fieldCode, SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $expectedDocuments = [$documentA, $documentC, $documentB];

        $this->assertEquals($expectedDocuments, $searchEngineResponse->getSearchDocuments()->getDocuments());
    }

    public function testMultivaluedFieldIsIndexedAndReturned()
    {
        $productId = ProductId::fromString('id');

        $fieldCode = 'code_' . uniqid();
        $fieldValueA = 'bar';
        $fieldValueB = 'baz';
        $fieldValues = [$fieldValueA, $fieldValueB];

        $document = $this->createSearchDocument([$fieldCode => $fieldValues], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($document);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldCode, $fieldValueA);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig($fieldCode, SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $documents = $searchEngineResponse->getSearchDocuments()->getDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($fieldValues, $documents[0]->getFieldsCollection()->getFields()[0]->getValues());
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();
}
