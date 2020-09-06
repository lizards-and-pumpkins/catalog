<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldValue;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\NoFacetFieldTransformationRegisteredException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\Util\Storage\Clearable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractSearchEngineTest extends TestCase
{
    /**
     * @var FacetFieldTransformationRegistry|MockObject
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
     * @param string[]|array[] $fields
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
        return new SortBy(AttributeCode::fromString($sortByFieldCode), SortDirection::create($sortDirection));
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

    private function assertFacetFieldHasValue(FacetField $field, string $expectedValue): void
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
    private function assertOrder(array $expectedOrder, array $actualArray): void
    {
        $keys = array_map(function ($value) use ($actualArray) {
            return array_search($value, $actualArray);
        }, $expectedOrder);
        $sortedKeys = $keys;
        sort($sortedKeys, SORT_NUMERIC);

        $this->assertSame($keys, $sortedKeys, 'Failed asserting elements order');
    }

    /**
     * @return FacetFiltersToIncludeInResult
     */
    private function createStubFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        $stubFacetFiltersToIncludeInResult = $this->createMock(FacetFiltersToIncludeInResult::class);
        $stubFacetFiltersToIncludeInResult->method('getAttributeCodeStrings')->willReturn([]);
        $stubFacetFiltersToIncludeInResult->method('getFields')->willReturn([]);

        return $stubFacetFiltersToIncludeInResult;
    }

    /**
     * @return QueryOptions
     */
    private function createStubQueryOptions() : QueryOptions
    {
        return $this->createStubQueryOptionsWithGivenContext($this->testContext);
    }

    /**
     * @param Context $context
     * @return QueryOptions
     */
    private function createStubQueryOptionsWithGivenContext(Context $context) : QueryOptions
    {
        $stubFacetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortBy = $this->createSortBy('product_id', SortDirection::ASC);

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
     * @return QueryOptions
     */
    private function createStubQueryOptionsWithGivenFacetFilters(
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        array $selectedFilters
    ) : QueryOptions {
        $stubSortBy = $this->createSortBy('product_id', SortDirection::ASC);

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
     * @return QueryOptions
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
     * @return QueryOptions
     */
    private function createStubQueryOptionsWithGivenPagination(int $rowsPerPage, int $pageNumber) : QueryOptions
    {
        $facetFiltersToIncludeInResult = $this->createStubFacetFiltersToIncludeInResult();
        $stubSortBy = $this->createSortBy('product_id', SortDirection::ASC);

        $stubQueryOptions = $this->createMock(QueryOptions::class);
        $stubQueryOptions->method('getFilterSelection')->willReturn([]);
        $stubQueryOptions->method('getContext')->willReturn($this->testContext);
        $stubQueryOptions->method('getFacetFiltersToIncludeInResult')->willReturn($facetFiltersToIncludeInResult);
        $stubQueryOptions->method('getRowsPerPage')->willReturn($rowsPerPage);
        $stubQueryOptions->method('getPageNumber')->willReturn($pageNumber);
        $stubQueryOptions->method('getSortBy')->willReturn($stubSortBy);

        return $stubQueryOptions;
    }

    final protected function setUp(): void
    {
        $this->stubFacetFieldTransformationRegistry = $this->createMock(FacetFieldTransformationRegistry::class);
        $this->searchEngine = $this->createSearchEngineInstance($this->stubFacetFieldTransformationRegistry);
        $this->testContext = $this->createContextFromDataParts([Website::CONTEXT_CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testSearchEngineResponseIsReturned(): void
    {
        $criteria = new SearchCriterionEqual('foo', 'bar');
        $result = $this->searchEngine->query($criteria, $this->createStubQueryOptions());

        $this->assertInstanceOf(SearchEngineResponse::class, $result);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned(): void
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

        $this->assertNotContains($productAId, $result);
        $this->assertContains($productBId, $result);
    }

    public function testPartialContextsAreMatched(): void
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

        $this->assertContains($productAId, $result);
        $this->assertContains($productBId, $result);
    }

    public function testEntriesContainingRequestedStringAreReturned(): void
    {
        $fieldName = 'baz';

        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $searchDocumentA = $this->createSearchDocument([$fieldName => 'Hidden bar here.'], $productAId);
        $searchDocumentB = $this->createSearchDocument([$fieldName => 'Here there is none.'], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $criteria = new SearchCriterionFullText('bar');

        $searchEngineResponse = $this->searchEngine->query($criteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria(): void
    {
        $searchCriteria = new SearchCriterionEqual('foo', 'some-value-which-is-definitely-absent-in-index');
        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());

        $this->assertCount(0, $searchEngineResponse->getProductIds());
    }

    /**
     * @dataProvider searchCriteriaProvider
     * @param SearchCriteria $searchCriteria
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingGivenCriteria(SearchCriteria $searchCriteria): void
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['foo' => 'baz'], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result);
        $this->assertNotContains($productBId, $result);
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
     * @param SearchCriteria $searchCriteria
     */
    public function testCollectionContainsOnlySearchDocumentsMatchingRangeCriteria(SearchCriteria $searchCriteria): void
    {
        $productAId = new ProductId(uniqid());
        $productBId = new ProductId(uniqid());

        $searchDocumentA = $this->createSearchDocument(['price' => 10], $productAId);
        $searchDocumentB = $this->createSearchDocument(['price' => 20], $productBId);

        $this->searchEngine->addDocument($searchDocumentA);
        $this->searchEngine->addDocument($searchDocumentB);

        $searchEngineResponse = $this->searchEngine->query($searchCriteria, $this->createStubQueryOptions());
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result);
        $this->assertNotContains($productBId, $result);
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

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned(): void
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

        $this->assertContains($productId, $result);
    }

    public function testItClearsTheStorage(): void
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

    public function testDocumentIsUniqueForProductIdAndContextCombination(): void
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
        $this->assertContains($productAId, $result);
        $this->assertContains($productBId, $result);
    }

    public function testFacetFieldCollectionOnlyContainsSpecifiedAttributes(): void
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

    public function testExceptionIsThrownIfNoTransformationIsRegisteredForRangedFacetField(): void
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

    public function testFacetFieldCollectionContainsConfiguredRanges(): void
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

    public function testOnlyProductsFromARequestedPageAreReturned(): void
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

    public function testSelectedFiltersAreAddedToCriteria(): void
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
        $this->assertContains($productAId, $result);
        $this->assertNotContains($productBId, $result);
    }

    public function testSelectedFiltersOptionValueSiblingsAreIncludedIntoFilterOptionValues(): void
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

    public function testSearchResultsAreSortedAccordingToGivenOrder(): void
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

        $sortBy = $this->createSortBy($fieldName, SortDirection::DESC);
        $selectedFilters = [];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);
        $expectedOrder = [$productAId, $productCId, $productBId];

        $this->assertOrder($expectedOrder, $searchEngineResponse->getProductIds());
    }

    public function testItReturnsAnEmptyArrayForRequestsWithSelectedFacetsIfTheSearchEngineIndexIsEmpty(): void
    {
        $criteria = new SearchCriterionAnything();

        $sortBy = $this->createSortBy('foo', SortDirection::DESC);
        $selectedFilters = ['foo' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertSame([], $searchEngineResponse->getProductIds());
    }

    public function testItDoesNotReturnAnyFacetsIfTheRequestedFacetFiltersAreEmpty(): void
    {
        $testProductId = new ProductId('ID');
        $fieldValue = ['foo', 'bar', 'baz'];

        $searchDocument = $this->createSearchDocument(['qux' => $fieldValue], $testProductId);
        $this->searchEngine->addDocument($searchDocument);
        
        $criteria = new SearchCriterionAnything();

        $sortBy = $this->createSortBy('foo', SortDirection::DESC);
        $selectedFilters = ['qux' => ['bar']];
        $queryOptions = $this->createStubQueryOptionsWithGivenSortOrder($sortBy, $selectedFilters);

        $searchEngineResponse = $this->searchEngine->query($criteria, $queryOptions);

        $this->assertContains($testProductId, $searchEngineResponse->getProductIds());
        $this->assertCount(0, $searchEngineResponse->getFacetFieldCollection());
    }

    public function testFacetFilterOptionValuesAreOrderedAlphabetically(): void
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

    public function testAppliesFacetFieldTransformationsToSelectedFilters(): void
    {
        $fieldCode = 'price';
        $fieldValue = '10-20';

        $mockFacetFieldTransformation = $this->createMock(FacetFieldTransformation::class);
        $mockFacetFieldTransformation->expects($this->once())->method('decode')
            ->willReturn(FacetFilterRange::create('10', '20'));

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(true);
        $this->stubFacetFieldTransformationRegistry->method('getTransformationByCode')
            ->willReturn($mockFacetFieldTransformation);

        $criteria = new SearchCriterionAnything();

        $filtersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestRangedField(AttributeCode::fromString($fieldCode))
        );
        $selectedFilters = [$fieldCode => [$fieldValue]];
        $queryOptions = $this->createStubQueryOptionsWithGivenFacetFilters($filtersToIncludeInResult, $selectedFilters);

        $this->searchEngine->query($criteria, $queryOptions);
    }
}
