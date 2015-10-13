<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

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
     * @param SearchDocument ...$searchDocuments
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection(SearchDocument ...$searchDocuments)
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getIterator')->willReturn(new \ArrayIterator($searchDocuments));
        $stubSearchDocumentCollection->method('getDocuments')->willReturn($searchDocuments);

        return $stubSearchDocumentCollection;
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
        $result = $this->searchEngine->query('baz', $this->testContext, []);
        $this->assertInstanceOf(SearchEngineResponse::class, $result);
    }

    public function testEmptyCollectionIsReturnedIfQueryStringIsNotFoundInIndex()
    {
        $searchDocumentFields = ['foo' => 'bar', 'baz' => 'qux'];
        $productId = ProductId::fromString(uniqid());
        $searchDocument = $this->createSearchDocument($searchDocumentFields, $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query('baz', $this->testContext, []);

        $this->assertCount(0, $searchEngineResponse->getSearchDocuments());
    }

    public function testSearchDocumentsAreAddedToAndRetrievedFromSearchEngine()
    {
        $keyword = 'bar';
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => $keyword], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query($keyword, $this->testContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testOnlyEntriesContainingRequestedStringAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query($keyword, $this->testContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productBId);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value-1']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value-2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value-2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => $keyword], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => $keyword], $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query($keyword, $queryContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionDoesNotContainDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testPartialContextsAreMatched()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());
        $documentAContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['locale' => 'value2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productBId, $documentBContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query('bar', $queryContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
        $this->assertCollectionContainsDocumentForProductId($result, $productBId);
    }

    public function testContextPartsThatAreNotInSearchDocumentContextAreIgnored()
    {
        $productId = ProductId::fromString(uniqid());
        $documentContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $queryContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);

        $searchDocument = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productId, $documentContext);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query('bar', $queryContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productId);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'Hidden bar here.'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'Here there is none.'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query('bar', $this->testContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productAId);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        $searchCriteria = SearchCriterionEqual::create('foo', 'some-value-which-is-definitely-absent-in-index');
        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $this->testContext,
            []
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

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $this->testContext,
            []
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
            [CompositeSearchCriterion::createAnd(
                SearchCriterionEqual::create('foo', 'bar'),
                SearchCriterionNotEqual::create('foo', 'baz')
            )],
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

        $searchEngineResponse = $this->searchEngine->getSearchDocumentsMatchingCriteria(
            $searchCriteria,
            $this->testContext,
            []
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
            [CompositeSearchCriterion::createAnd(
                SearchCriterionGreaterThan::create('price', 5),
                SearchCriterionLessOrEqualThan::create('price', 10)
            )],
            [CompositeSearchCriterion::createAnd(
                SearchCriterionGreaterOrEqualThan::create('price', 10),
                SearchCriterionLessThan::create('price', 20)
            )],
        ];
    }

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned()
    {
        $productId = ProductId::fromString(uniqid());

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'bar'], $productId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query('bar', $this->testContext, []);
        $result = $searchEngineResponse->getSearchDocuments();

        $this->assertCollectionContainsDocumentForProductId($result, $productId);
    }

    public function testItClearsTheStorage()
    {
        $searchDocumentFieldName = 'foo';
        $searchDocumentFieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $productId
        );

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocument);
        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $this->searchEngine->clear();
        $searchEngineResponse = $this->searchEngine->query($searchDocumentFieldValue, $this->testContext, []);

        $this->assertEmpty($searchEngineResponse->getSearchDocuments());
    }

    public function testDocumentIsUniqueForProductIdAndContextCombination()
    {
        $productAId = ProductId::fromString(uniqid());
        $productBId = ProductId::fromString(uniqid());

        $uniqueValue = uniqid();
        $documentFields = ['foo' => $uniqueValue];

        $searchDocumentA = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);
        $searchDocumentB = $this->createSearchDocumentWithContext($documentFields, $productBId, $this->testContext);
        $searchDocumentC = $this->createSearchDocumentWithContext($documentFields, $productAId, $this->testContext);

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection(
            $searchDocumentA,
            $searchDocumentB,
            $searchDocumentC
        );

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query($uniqueValue, $this->testContext, []);
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

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['bar' => $keyword, 'baz' => 'qux'], $productBId);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($searchDocumentA, $searchDocumentB);

        $this->searchEngine->addSearchDocumentCollection($stubSearchDocumentCollection);
        $searchEngineResponse = $this->searchEngine->query($keyword, $this->testContext, ['foo', 'bar']);

        $expectedFooFacetField = new SearchEngineFacetField(
            AttributeCode::fromString('foo'),
            SearchEngineFacetFieldValueCount::create($keyword, 1)
        );
        $expectedBarFacetField = new SearchEngineFacetField(
            AttributeCode::fromString('bar'),
            SearchEngineFacetFieldValueCount::create($keyword, 1)
        );
        $result = $searchEngineResponse->getFacetFieldCollection();

        $this->assertCount(2, $result->getFacetFields());
        $this->assertContains($expectedFooFacetField, $result->getFacetFields(), '', false, false);
        $this->assertContains($expectedBarFacetField, $result->getFacetFields(), '', false, false);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();
}
