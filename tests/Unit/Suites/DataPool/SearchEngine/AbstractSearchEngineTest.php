<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
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

    abstract protected function createSearchEngineInstance(
        FacetFieldTransformationRegistry $facetFieldTransformationRegistry
    ) : SearchEngine;

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @return SearchDocument
     */
    private function createSearchDocument(array $fields, ProductId $productId) : SearchDocument
    {
        return $this->createSearchDocumentWithContext($fields, $productId, $this->testContext);
    }

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @param Context $context
     * @return SearchDocument
     */
    private function createSearchDocumentWithContext(
        array $fields,
        ProductId $productId,
        Context $context
    ) : SearchDocument {
        return new SearchDocument(SearchDocumentFieldCollection::fromArray($fields), $context, $productId);
    }

    /**
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataParts(array $contextDataSet) : Context
    {
        $contextDataSet[DataVersion::CONTEXT_CODE] = '-1';
        return SelfContainedContextBuilder::rehydrateContext($contextDataSet);
    }

    private function createSortBy(string $sortByFieldCode, string $sortDirection) : SortBy
    {
        return SortBy::createUnselected(
            AttributeCode::fromString($sortByFieldCode),
            SortOrderDirection::create($sortDirection)
        );
    }

    private function assertFacetFieldCollectionContainsFieldWithCodeAndValue(
        FacetFieldCollection $facetFieldCollection,
        string $code,
        string $value
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

    private function assertFacetFieldHasValue(FacetField $field, string $expectedValue)
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
    private function assertOrder(array $expectedOrder, array $actualArray)
    {
        $keys = array_map(function ($value) use ($actualArray) {
            return array_search($value, $actualArray);
        }, $expectedOrder);
        $sortedKeys = $keys;
        sort($sortedKeys, SORT_NUMERIC);

        $this->assertSame($keys, $sortedKeys, 'Failed asserting elements order');
    }

    /**
     * @return FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        $stubFacetFiltersToIncludeInResult = $this->createMock(FacetFiltersToIncludeInResult::class);
        $stubFacetFiltersToIncludeInResult->method('getAttributeCodeStrings')->willReturn([]);
        $stubFacetFiltersToIncludeInResult->method('getFields')->willReturn([]);

        return $stubFacetFiltersToIncludeInResult;
    }

    /**
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptions() : QueryOptions
    {
        return $this->createStubQueryOptionsWithGivenContext($this->testContext);
    }

    /**
     * @param Context $context
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenContext(Context $context) : QueryOptions
    {
        $stubFacetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortBy = $this->createSortBy('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->createMock(QueryOptions::class);
        $stubQueryOptions->method('getFilterSelection')->willReturn([]);
        $stubQueryOptions->method('getContext')->willReturn($context);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($stubFacetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortBy')->willReturn($stubSortBy);

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
    ) : QueryOptions {
        $stubSortBy = $this->createSortBy('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->createMock(QueryOptions::class);
        $stubQueryOptions->method('getFilterSelection')->willReturn($selectedFilters);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($facetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortBy')->willReturn($stubSortBy);

        return $stubQueryOptions;
    }

    /**
     * @param SortBy $sortBy
     * @param array[] $selectedFilters
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenSortOrder(SortBy $sortBy, array $selectedFilters) : QueryOptions
    {
        $stubFacetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();

        $stubQueryOptions = $this->createMock(QueryOptions::class);
        $stubQueryOptions->method('getFilterSelection')->willReturn($selectedFilters);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($stubFacetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn(100);
        $stubQueryOptions->method('getPageNumber')->willReturn(0);
        $stubQueryOptions->method('getSortBy')->willReturn($sortBy);

        return $stubQueryOptions;
    }

    /**
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubQueryOptionsWithGivenPagination(int $rowsPerPage, int $pageNumber) : QueryOptions
    {
        $facetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortBy = $this->createSortBy('product_id', SortOrderDirection::ASC);

        $stubQueryOptions = $this->createMock(QueryOptions::class);
        $stubQueryOptions->method('getFilterSelection')->willReturn([]);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($facetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn($rowsPerPage);
        $stubQueryOptions->method('getPageNumber')->willReturn($pageNumber);
        $stubQueryOptions->method('getSortBy')->willReturn($stubSortBy);

        return $stubQueryOptions;
    }

    protected function setUp()
    {
        $this->stubFacetFieldTransformationRegistry = $this->createMock(FacetFieldTransformationRegistry::class);
        $this->searchEngine = $this->createSearchEngineInstance($this->stubFacetFieldTransformationRegistry);
        $this->testContext = $this->createContextFromDataParts([Website::CONTEXT_CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testSearchEngineResponseIsReturned()
    {
        $criteria = new SearchCriterionEqual('foo', 'bar');
        $result = $this->searchEngine->query($criteria, $this->createStubQueryOptions());

        $this->assertInstanceOf(SearchEngineResponse::class, $result);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value-1']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value-2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value-2']);

        $documentFields = [$fieldName => $fieldValue];
        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $documentBContext);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = new SearchCriterionEqual($fieldName, $fieldValue);
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

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());
        $documentAContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts([ 'locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);

        $documentFields = [$fieldName => $fieldValue];
        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $documentBContext);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = new SearchCriterionEqual($fieldName, $fieldValue);
        $queryOptions = $this->createStubQueryOptionsWithGivenContext($queryContext);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
        $this->assertContains($productBId, $result, '', false, false);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $fieldName = 'baz';

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

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
        $searchCriteria = new SearchCriterionEqual('foo', 'some-value-which-is-definitely-absent-in-index');
        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());

        $this->assertCount(0, $searchEngineResponse->getProductIds());
    }

    /**
     * @dataProvider searchCriteriaProvider
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingGivenCriteria(SearchCriteria $searchCriteria)
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

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
    public function searchCriteriaProvider() : array
    {
        return [
            [new SearchCriterionEqual('foo', 'bar')],
            [new SearchCriterionNotEqual('foo', 'baz')],
            [
                CompositeSearchCriterion::createAnd(
                    new SearchCriterionEqual('foo', 'bar'),
                    new SearchCriterionNotEqual('foo', 'baz')
                )
            ],
        ];
    }

    /**
     * @dataProvider searchRangeCriteriaProvider
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingRangeCriteria(SearchCriteria $searchCriteria)
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

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
    public function searchRangeCriteriaProvider() : array
    {
        return [
            [new SearchCriterionLessThan('price', 20)],
            [new SearchCriterionLessOrEqualThan('price', 10)],
            [
                CompositeSearchCriterion::createAnd(
                    new SearchCriterionGreaterThan('price', 5),
                    new SearchCriterionLessOrEqualThan('price', 10)
                )
            ],
            [
                CompositeSearchCriterion::createAnd(
                    new SearchCriterionGreaterOrEqualThan('price', 10),
                    new SearchCriterionLessThan('price', 20)
                )
            ],
        ];
    }

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $productId = new ProductId(uniqid());

        $searchDocumentA = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => $fieldValue], $productId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = new SearchCriterionEqual($fieldName, $fieldValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productId, $result, '', false, false);
    }

    public function testItClearsTheStorage()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $productId = new ProductId('id');

        $searchDocument = $this->createSearchDocument([$fieldName => $fieldValue], $productId);
        $this->searchEngine->addDocument($searchDocument);
        $this->searchEngine->clear();

        $criteria = new SearchCriterionEqual($fieldName, $fieldValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());

        $this->assertCount(0, $searchEngineResponse->getProductIds());
    }

    public function testDocumentIsUniqueForProductIdAndContextCombination()
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $fieldName = 'foo';
        $uniqueValue = uniqid();
        $documentFields = [$fieldName => $uniqueValue];

        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $this->testContext);
        $searchDocumentC = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);
        $this->searchEngine->addDocument($searchDocumentC);

        $criteria = new SearchCriterionEqual($fieldName, $uniqueValue);

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertCount(2, $result);
        $this->assertContains($productAId, $result, '', false, false);
        $this->assertContains($productBId, $result, '', false, false);
    }

    public function testFacetFieldCollectionOnlyContainsSpecifiedAttributes()
    {
        $keyword = uniqid();
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $fieldACode = 'foo';
        $fieldBCode = 'bar';
        $fieldCCode = 'baz';

        $searchDocumentA = $this->createSearchDocument([$fieldACode => $keyword, $fieldBCode => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldBCode => $keyword, $fieldCCode => 'test'], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual($fieldACode, $keyword),
            new SearchCriterionEqual($fieldBCode, $keyword),
            new SearchCriterionEqual($fieldCCode, $keyword)
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
        $productId = new ProductId('id');

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

        $criteria = new SearchCriterionGreaterOrEqualThan($fieldName, $fieldValue);

        $this->expectException(NoFacetFieldTransformationRegisteredException::class);

        $this->searchEngine->query($criteria, $queryOptions);
    }

    public function testFacetFieldCollectionContainsConfiguredRanges()
    {
        $productAId = new ProductId('A');
        $productBId = new ProductId('B');
        $productCId = new ProductId('C');

        $fieldName = 'price';
        $fieldValue = 0;

        $searchDocumentA = $this->createSearchDocument([$fieldName => 1], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => 11], $productBId);
        $searchDocumentC = $this->createSearchDocument([$fieldName => 31], $productCId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);
        $this->searchEngine->addDocument($searchDocumentC);

        $stubFacetFieldTransformation = $this->createMock(FacetFieldTransformation::class);
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

        $criteria = new SearchCriterionGreaterOrEqualThan($fieldName, $fieldValue);

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

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $searchDocumentA = $this->createSearchDocument([$field => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument([$field => $keyword], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = new SearchCriterionEqual($field, $keyword);

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

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $fieldACode = 'foo';
        $fieldBCode = 'bar';

        $documentA = $this->createSearchDocument([$fieldACode => $keywordA, $fieldBCode => $keywordB], $productAId);
        $documentB = $this->createSearchDocument([$fieldACode => $keywordA], $productBId);

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);

        $criteria = new SearchCriterionEqual($fieldACode, $keywordA);

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

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $documentA = $this->createSearchDocument([$fieldACode => $fieldValueA, $fieldBCode => $keyword], $productAId);
        $documentB = $this->createSearchDocument([$fieldACode => $fieldValueB, $fieldBCode => $keyword], $productBId);

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode))
        );
        $selectedFilters = [$fieldACode => [$fieldValueB]];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $criteria = new SearchCriterionEqual($fieldBCode, $keyword);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $result = $searchEngineResponse->getFacetFieldCollection()->getFacetFields();

        $this->assertCount(1, $result);
        $this->assertFacetFieldHasValue($result[0], $fieldValueA);
        $this->assertFacetFieldHasValue($result[0], $fieldValueB);
    }

    public function testSearchResultsAreSortedAccordingToGivenOrder()
    {
        $productAId = new ProductId('A');
        $productBId = new ProductId('B');
        $productCId = new ProductId('C');

        $fieldName = 'price';
        $fieldValue = 0;

        $documentA = $this->createSearchDocument([$fieldName => 3], $productAId);
        $documentB = $this->createSearchDocument([$fieldName => 1], $productBId);
        $documentC = $this->createSearchDocument([$fieldName => 2], $productCId);

        $this->searchEngine->addDocument($documentA);
        $this->searchEngine->addDocument($documentB);
        $this->searchEngine->addDocument($documentC);

        $criteria = new SearchCriterionGreaterOrEqualThan($fieldName, $fieldValue);

        $sortBy = $this->createSortBy($fieldName, SortOrderDirection::DESC);
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $expectedOrder = [$productAId, $productCId, $productBId];

        $this->assertOrder($expectedOrder, $searchEngineResponse->getProductIds());
    }

    public function testItReturnsAnEmptyArrayForRequestsWithSelectedFacetsIfTheSearchEngineIndexIsEmpty()
    {
        $criteria = new SearchCriterionAnything();

        $sortBy = $this->createSortBy('foo', SortOrderDirection::DESC);
        $selectedFilters = ['foo' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertSame([], $searchEngineResponse->getProductIds());
    }

    public function testItDoesNotReturnAnyFacetsIfTheRequestedFacetFiltersAreEmpty()
    {
        $fieldValue = ['foo', 'bar', 'baz'];

        $searchDocument = $this->createSearchDocument(['qux' => $fieldValue], new ProductId('ID'));
        $this->searchEngine->addDocument($searchDocument);
        
        $criteria = new SearchCriterionAnything();

        $sortBy = $this->createSortBy('foo', SortOrderDirection::DESC);
        $selectedFilters = ['qux' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertContains('ID', $searchEngineResponse->getProductIds());
        $this->assertCount(0, $searchEngineResponse->getFacetFieldCollection());
    }

    public function testFacetFilterOptionValuesAreOrderedAlphabetically()
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $fieldCode = 'foo';

        $facetValueA = 'baz';
        $facetValueB = 'bar';

        $searchDocumentA = $this->createSearchDocument([$fieldCode => $facetValueA], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldCode => $facetValueB], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual($fieldCode, $facetValueA),
            new SearchCriterionEqual($fieldCode, $facetValueB)
        );

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldCode))
        );
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $facetFields = $searchEngineResponse->getFacetFieldCollection()->getFacetFields();
        $expectedValues = [
            new FacetFieldValue($facetValueB, 1),
            new FacetFieldValue($facetValueA, 1),
        ];

        $this->assertEquals($expectedValues, $facetFields[0]->getValues());
    }
}
