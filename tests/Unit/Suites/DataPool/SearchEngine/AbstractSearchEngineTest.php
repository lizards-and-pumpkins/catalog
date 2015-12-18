<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformation;
use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextVersion;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
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
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLike;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Utils\Clearable;

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
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
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
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $queryContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $queryContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionLike::create($fieldName, 'bar');
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productAId, $result, '', false, false);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        $searchCriteria = SearchCriterionEqual::create('foo', 'some-value-which-is-definitely-absent-in-index');
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $result = $searchEngineResponse->getProductIds();

        $this->assertContains($productId, $result, '', false, false);
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
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

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

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection(
            $searchDocumentA,
            $searchDocumentB,
            $searchDocumentC
        );

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($fieldName, $uniqueValue);
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = CompositeSearchCriterion::createOr(
            SearchCriterionEqual::create($fieldACode, $keyword),
            SearchCriterionEqual::create($fieldBCode, $keyword),
            SearchCriterionEqual::create($fieldCCode, $keyword)
        );

        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode)),
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldBCode))
        );

        $selectedFilters = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $result = $searchEngineResponse->getFacetFieldCollection();

        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($result, $fieldACode, $keyword);
        $this->assertFacetFieldCollectionContainsFieldWithCodeAndValue($result, $fieldBCode, $keyword);
    }

    public function testExceptionIsThrownIfNoTransformationIsRegisteredForRangedFacetField()
    {
        $productId = ProductId::fromString('id');

        $fieldName = 'price';
        $fieldValue = 0;

        $document = $this->createSearchDocument([$fieldName => 1], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($document);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(false);

        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestRangedField(
                AttributeCode::fromString($fieldName),
                FacetFilterRange::create(1, 10)
            )
        );

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $this->setExpectedException(NoFacetFieldTransformationRegisteredException::class);

        $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
    }

    public function testFacetFieldCollectionContainsConfiguredRanges()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');

        $fieldName = 'price';
        $fieldValue = 0;

        $documentA = $this->createSearchDocument([$fieldName => 1], $productAId);
        $documentB = $this->createSearchDocument([$fieldName => 11], $productBId);
        $documentC = $this->createSearchDocument([$fieldName => 31], $productCId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $stubFacetFieldTransformation = $this->getMock(FacetFieldTransformation::class);
        $stubFacetFieldTransformation->method('encode')->willReturnCallback(function (FacetFilterRange $range) {
            return sprintf('%s-%s', $range->from(), $range->to());
        });

        $this->stubFacetFieldTransformationRegistry->method('hasTransformationForCode')->willReturn(true);
        $this->stubFacetFieldTransformationRegistry->method('getTransformationByCode')
            ->willReturn($stubFacetFieldTransformation);

        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestRangedField(
                AttributeCode::fromString($fieldName),
                FacetFilterRange::create(null, 10),
                FacetFilterRange::create(10, 20),
                FacetFilterRange::create(20, 30),
                FacetFilterRange::create(30, null)
            )
        );

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

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

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionEqual::create($field, $keyword);
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 1;
        $pageNumber = 1;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $this->assertCount(1, $searchEngineResponse->getProductIds());
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

        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode)),
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldBCode))
        );

        $selectedFilters = [$fieldBCode => [$keywordB]];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString($fieldACode))
        );

        $criteria = SearchCriterionEqual::create($fieldBCode, $keyword);
        $selectedFilters = [$fieldACode => [$fieldValueB]];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('product_id', SortOrderDirection::ASC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

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
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($documentA, $documentB, $documentC);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);

        $criteria = SearchCriterionGreaterOrEqualThan::create($fieldName, $fieldValue);
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig($fieldName, SortOrderDirection::DESC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $expectedOrder = [$productAId, $productCId, $productBId];

        $this->assertOrder($expectedOrder, $searchEngineResponse->getProductIds());
    }

    public function testItReturnsAnEmptyArrayForRequestsWithSelectedFacetsIfTheSearchEngineIndexIsEmpty()
    {
        $criteria = SearchCriterionAnything::create();
        $selectedFilters = ['foo' => ['bar']];
        $facetFilterRequest = new FacetFiltersToIncludeInResult();
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = $this->createStubSortOrderConfig('foo', SortOrderDirection::DESC);

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->testContext,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
        $this->assertSame([], $searchEngineResponse->getProductIds());
    }
}
