<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Context\Website\ContextWebsite;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\NoFacetFieldTransformationRegisteredException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\Util\Storage\Clearable;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FacetFieldTransformationRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFacetFieldTransformationRegistry;

    /**
     * @var Context
     */
    private $testContext;

    /**
     * @var SearchEngine|Clearable
     */
    private $searchEngine;

    /**
     * @param FacetFieldTransformationRegistry $facetFieldTransformationRegistry
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance(
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    );

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
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataParts(array $contextDataSet)
    {
        $contextDataSet[ContextVersion::CODE] = '-1';
        return SelfContainedContextBuilder::rehydrateContext($contextDataSet);
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

    /**
     * @param FacetFieldCollection $facetFieldCollection
     * @param string $code
     * @param string $value
     */
    private function assertFacetFieldCollectionContainsFieldWithCodeAndValue(
        FacetFieldCollection $facetFieldCollection,
        $code,
        $value
    ) {
        foreach ($facetFieldCollection as $facetField) {
            /** @var FacetField $facetField */
            if ((string) $facetField->getAttributeCode() === $code) {
                $this->assertFacetFieldHasValue($facetField, $value);
                return;
            }
        }

        $this->fail(sprintf(
            'Failed asserting facet field collection contains field with code "%s" and value "%s.',
            $code,
            $value
        ));
    }

    /**
     * @param FacetField $field
     * @param string $expectedValue
     */
    private function assertFacetFieldHasValue(FacetField $field, $expectedValue)
    {
        foreach ($field->getValues() as $value) {
            if ($value->jsonSerialize()['value'] === $expectedValue) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail(sprintf('Failed asserting facet field has value "%s.', $expectedValue));
    }

    /**
     * @param ProductId[] $expectedOrder
     * @param ProductId[] $actualArray
     */
    private function assertOrder(array $expectedOrder, $actualArray)
    {
        $keys = array_map(function ($value) use ($actualArray) {
            return array_search($value, $actualArray);
        }, $expectedOrder);
        $sortedKeys = $keys;
        sort($sortedKeys, SORT_NUMERIC);

        $this->assertSame($keys, $sortedKeys, 'Failed asserting elements order');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetFiltersToIncludeInResult()
    {
        $stubFacetFiltersToIncludeInResult = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);
        $stubFacetFiltersToIncludeInResult->method('getAttributeCodeStrings')->willReturn([]);
        $stubFacetFiltersToIncludeInResult->method('getFields')->willReturn([]);

        return $stubFacetFiltersToIncludeInResult;
    }

    /**
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptions()
    {
        return $this->createStubQueryOptionsWithGivenContext($this->testContext);
    }

    /**
     * @param Context $context
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenContext(Context $context)
    {
        $stubFacetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);
        $stubQueryOptions->method('getFilterSelection')->willReturn([]);
        $stubQueryOptions->method('getContext')->willReturn($context);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($stubFacetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortOrderConfig')->willReturn($stubSortOrderConfig);

        return $stubQueryOptions;
    }

    /**
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param array[] $selectedFilters
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenFacetFilters(
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        array $selectedFilters
    ) {
        $stubSortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);
        $stubQueryOptions->method('getFilterSelection')->willReturn($selectedFilters);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($facetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortOrderConfig')->willReturn($stubSortOrderConfig);

        return $stubQueryOptions;
    }

    /**
     * @param SortOrderConfig $sortOrderConfig
     * @param array[] $selectedFilters
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenSortOrder(SortOrderConfig $sortOrderConfig, array $selectedFilters)
    {
        $stubFacetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();

        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);
        $stubQueryOptions->method('getFilterSelection')->willReturn($selectedFilters);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($stubFacetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortOrderConfig')->willReturn($sortOrderConfig);

        return $stubQueryOptions;
    }

    /**
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenPagination($rowsPerPage, $pageNumber)
    {
        $facetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);
        $stubQueryOptions->method('getFilterSelection')->willReturn([]);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($facetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn($rowsPerPage);
        $stubQueryOptions->method('getPageNumber')->willReturn($pageNumber);
        $stubQueryOptions->method('getSortOrderConfig')->willReturn($stubSortOrderConfig);

        return $stubQueryOptions;
    }

    protected function setUp()
    {
        $this->stubFacetFieldTransformationRegistry = $this->getMock(FacetFieldTransformationRegistry::class);
        $this->searchEngine = $this->createSearchEngineInstance($this->stubFacetFieldTransformationRegistry);
        $this->testContext = $this->createContextFromDataParts([ContextWebsite::CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testSearchEngineResponseIsReturned()
    {
        $criteria = SearchCriterionEqual::create('foo', 'bar');
        $result = $this->searchEngine->query($criteria, $this->createStubQueryOptions());

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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $queryOptions = $this->createStubQueryOptionsWithGivenContext($queryContext);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getProductIds();

        $this->assertNotContains($productAId, $result, '', false, false);
        $this->assertContains($productBId, $result, '', false, false);
    }

    public function testPartialContextsAreMatched()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts([ 'locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);

        $documentFields = [$fieldName => $fieldValue];
        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $documentBContext);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $queryOptions = $this->createStubQueryOptionsWithGivenContext($queryContext);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
        $this->assertContains($productBId, $result, '', false, false);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $fieldName = 'baz';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument([$fieldName => 'Hidden bar here.'], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => 'Here there is none.'], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $searchEngineResponse = $this->searchEngine->queryFullText('bar', $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        $searchCriteria = SearchCriterionEqual::create('foo', 'some-value-which-is-definitely-absent-in-index');
        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());

        $this->assertCount(0, $searchEngineResponse->getProductIds());
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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
        $this->assertNotContains($productBId, $result, '', false, false);
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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
        $this->assertNotContains($productBId, $result, '', false, false);
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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productId, $result, '', false, false);
    }

    public function testItClearsTheStorage()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $this->searchEngine->addDocument($searchDocument);
        $this->searchEngine->clear();

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());

        $this->assertCount(0, $searchEngineResponse->getProductIds());
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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);
        $this->searchEngine->addDocument($searchDocumentC);

        $criteria = SearchCriterionEqual::create($fieldName, $uniqueValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertCount(2, $result);
        $this->assertContains($productAId, $result, '', false, false);
        $this->assertContains($productBId, $result, '', false, false);
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

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create($fieldACode, $keyword),
            SearchCriterionEqual::create($fieldBCode, $keyword),
            SearchCriterionEqual::create($fieldCCode, $keyword)
        );

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode)),
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldBCode))
        );
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getFacetFieldCollection();

        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($result, $fieldACode, $keyword);
        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($result, $fieldBCode, $keyword);
    }

    public function testExceptionIsThrownIfNoTransformationIsRegisteredForRangedFacetField()
    {
        $productId = ProductId::fromString('id');

        $fieldName = 'price';
        $fieldValue = 0;

        $searchDocument = $this->createSearchDocument([$fieldName => 1], $productId);
        $this->searchEngine->addDocument($searchDocument);

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(false);

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestRangedField(
                AttributeCode::fromString($fieldName),
                FacetFilterRange::create(1, 10)
            )
        );
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);

        $this->expectException(NoFacetFieldTransformationRegisteredException::class);

        $this->searchEngine->query($criteria, $queryOptions);
    }

    public function testFacetFieldCollectionContainsConfiguredRanges()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldName = 'price';
        $fieldValue = 0;

        $searchDocumentA = $this->createSearchDocument([$fieldName => 1], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => 11], $productBId);
        $searchDocumentC = $this->createSearchDocument([$fieldName => 31], $productCId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);
        $this->searchEngine->addDocument($searchDocumentC);

        $stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
        $stubFacetFieldTransformation->method('encode')->willReturnCallback(function (FacetFilterRange $range) {
            return sprintf('%s-%s', $range->from(), $range->to());
        });

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(true);
        $this->stubFacetFieldTransformationRegistry->method('getTransformationByCode')
            ->willReturn($stubFacetFieldTransformation);

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestRangedField(
                AttributeCode::fromString($fieldName),
                FacetFilterRange::create(null, 10),
                FacetFilterRange::create(10, 20),
                FacetFilterRange::create(20, 30),
                FacetFilterRange::create(30, null)
            )
        );
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $facetFieldsCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($facetFieldsCollection, $fieldName, '-10');
        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($facetFieldsCollection, $fieldName, '10-20');
        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($facetFieldsCollection, $fieldName, '30-');
    }

    public function testOnlyProductsFromARequestedPageAreReturned()
    {
        $field = 'foo';
        $keyword = uniqid();

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument([$field => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument([$field => $keyword], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = SearchCriterionEqual::create($field, $keyword);

        $rowsPerPage = 1;
        $pageNumber = 1;
        $queryOptions = $this->createStubQueryOptionsWithGivenPagination($rowsPerPage, $pageNumber);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertCount($rowsPerPage, $searchEngineResponse->getProductIds());
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

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);

        $criteria = SearchCriterionEqual::create($fieldACode, $keywordA);

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode)),
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldBCode))
        );
        $selectedFilters = [$fieldBCode => [$keywordB]];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getProductIds();

        $this->assertCount(1, $result);
        $this->assertContains($productAId, $result, '', false, false);
        $this->assertNotContains($productBId, $result, '', false, false);
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

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode))
        );
        $selectedFilters = [$fieldACode => [$fieldValueB]];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $criteria = SearchCriterionEqual::create($fieldBCode, $keyword);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getFacetFieldCollection()->getFacetFields();

        $this->assertCount(1, $result);
        $this->assertFacetFieldHasValue($result[0], $fieldValueA);
        $this->assertFacetFieldHasValue($result[0], $fieldValueB);
    }

    public function testSearchResultsAreSortedAccordingToGivenOrder()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldName = 'price';
        $fieldValue = 0;

        $documentA = $this->createSearchDocument([$fieldName => 3], $productAId);
        $documentB = $this->createSearchDocument([$fieldName => 1], $productBId);
        $documentC = $this->createSearchDocument([$fieldName => 2], $productCId);

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);
        $this->searchEngine->addDocument($documentC);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);

        $sortOrderConfig = $this->createStubSortOrderConfig($fieldName, SortOrderDirection::DESC);
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortOrderConfig, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $expectedOrder = [$productAId, $productCId, $productBId];

        $this->assertOrder($expectedOrder, $searchEngineResponse->getProductIds());
    }

    public function testItReturnsAnEmptyArrayForRequestsWithSelectedFacetsIfTheSearchEngineIndexIsEmpty()
    {
        $criteria = SearchCriterionAnything::create();

        $sortOrderConfig = $this->createStubSortOrderConfig('foo', SortOrderDirection::DESC);
        $selectedFilters = ['foo' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortOrderConfig, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertSame([], $searchEngineResponse->getProductIds());
    }

    public function testItDoesNotReturnAnyFacetsIfTheRequestedFacetFiltersAreEmpty()
    {
        $fieldValue = ['foo', 'bar', 'baz'];

        $searchDocument = $this->createSearchDocument(['qux' => $fieldValue], ProductId::fromString('ID'));
        $this->searchEngine->addDocument($searchDocument);
        
        $criteria = SearchCriterionAnything::create();

        $sortOrderConfig = $this->createStubSortOrderConfig('foo', SortOrderDirection::DESC);
        $selectedFilters = ['qux' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortOrderConfig, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertContains('ID', $searchEngineResponse->getProductIds());
        $this->assertCount(0, $searchEngineResponse->getFacetFieldCollection());
    }

    public function testFacetFilterOptionValuesAreOrderedAlphabetically()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $fieldCode = 'foo';

        $facetValueA = 'baz';
        $facetValueB = 'bar';

        $searchDocumentA = $this->createSearchDocument([$fieldCode => $facetValueA], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldCode => $facetValueB], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create($fieldCode, $facetValueA),
            SearchCriterionEqual::create($fieldCode, $facetValueB)
        );

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldCode))
        );
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $facetFields = $searchEngineResponse->getFacetFieldCollection()->getFacetFields();
        $expectedValues = [
            FacetFieldValue::create($facetValueB, 1),
            FacetFieldValue::create($facetValueA, 1),
        ];

        $this->assertEquals($expectedValues, $facetFields[0]->getValues());
    }
}
