<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Context\WebsiteContextDecorator;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use Brera\DataVersion;
use Brera\Product\ProductId;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var Context
     */
    private $testContext;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    protected function setUp()
    {
        $this->searchEngine = $this->createSearchEngineInstance();
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $this->testContext = $this->createContextFromDataParts([WebsiteContextDecorator::CODE => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testEmptyCollectionIsReturnedRegardlessOfWhatHasBeenQueriedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar', $this->testContext);
        $this->assertEmpty($result);
    }

    public function testEntryIsAddedIntoIndexAndThenFound()
    {
        $searchDocumentFieldName = 'foo';
        $searchDocumentFieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $productId
        );

        $this->searchEngine->addSearchDocument($searchDocument);
        $result = $this->searchEngine->query($searchDocumentFieldValue, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productId]);
    }

    public function testEmptyCollectionIsReturnedIfQueryStringIsNotFoundInIndex()
    {
        $searchDocumentFields = ['foo' => 'bar'];
        $productId = ProductId::fromString('id');
        $searchDocument = $this->createSearchDocument($searchDocumentFields, $productId);
        $this->searchEngine->addSearchDocument($searchDocument);
        $result = $this->searchEngine->query('baz', $this->testContext);

        $this->assertEmpty($result);
    }

    public function testMultipleEntriesAreAddedToIndex()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => $keyword], $productBId);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testOnlyEntriesContainingRequestedStringAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productBId);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId]);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $keyword = 'bar';

        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $documentAContext = $this->createContextFromDataParts(['website' => 'value-1']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value-2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => $keyword], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => $keyword], $productBId, $documentBContext);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $queryContext = $this->createContextFromDataParts(['website' => 'value-2']);
        $result = $this->searchEngine->query($keyword, $queryContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productBId]);
    }

    public function testPartialContextsAreMatched()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $documentAContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $documentBContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);

        $searchDocumentA = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productAId, $documentAContext);
        $searchDocumentB = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productBId, $documentBContext);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $queryContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $result = $this->searchEngine->query('bar', $queryContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testContextPartsThatAreNotInSearchDocumentContextAreIgnored()
    {
        $productId = ProductId::fromString('id');
        $documentContext = $this->createContextFromDataParts(['locale' => 'value2']);
        $searchDocument = $this->createSearchDocumentWithContext(['foo' => 'bar'], $productId, $documentContext);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocument]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $queryContext = $this->createContextFromDataParts(['website' => 'value1', 'locale' => 'value2']);
        $result = $this->searchEngine->query('bar', $queryContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productId]);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $productAId = ProductId::fromString('id01');
        $productBId = ProductId::fromString('id02');

        $searchDocumentA = $this->createSearchDocument(['foo' => 'barbarism'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'cabaret'], $productBId);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testEmptyCollectionIsReturnedForEmptySearchCriteria()
    {
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, []);
        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($mockCriteria, $this->testContext);

        $this->assertEmpty($result);
    }

    public function testEmptyCollectionIsReturnedIfNoMatchesAreFound()
    {
        $criterion = SearchCriterion::create('test-field', 'test-search-term', '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($mockCriteria, $this->testContext);

        $this->assertEmpty($result);
    }

    public function testCollectionWithOneDocumentMatchingCriteriaIsReturned()
    {
        $productId = ProductId::fromString('id');
        $dummyFieldName = 'test-field-name';
        $dummyQueryTerm = 'test-query-term';

        $searchDocument = $this->createSearchDocument([$dummyFieldName => $dummyQueryTerm], $productId);
        $this->searchEngine->addSearchDocument($searchDocument);

        $criterion = SearchCriterion::create($dummyFieldName, $dummyQueryTerm, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);
        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($mockCriteria, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productId]);
    }

    public function testCollectionWithTwoDocumentsMatchingAnyCriteriaIsReturned()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $dummyFieldName1 = 'foo';
        $dummyFieldValue1 = 'bar';
        $dummyFieldName2 = 'baz';
        $dummyFieldValue2 = 'qux';

        $searchDocumentA = $this->createSearchDocument([$dummyFieldName1 => $dummyFieldValue1], $productAId);
        $searchDocumentB = $this->createSearchDocument([$dummyFieldName2 => $dummyFieldValue2], $productBId);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $criterion1 = SearchCriterion::create($dummyFieldName1, $dummyFieldValue1, '=');
        $criterion2 = SearchCriterion::create($dummyFieldName2, $dummyFieldValue2, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion1, $criterion2]);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($mockCriteria, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testCollectionWithTwoDocumentsMatchingAllCriteriaAreReturned()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');
        $productCId = ProductId::fromString('C');
        $dummyFieldName1 = 'foo';
        $dummyFieldValue1 = 'bar';
        $dummyFieldName2 = 'baz';
        $dummyFieldValue2 = 'qux';

        $searchDocumentA = $this->createSearchDocument([
            $dummyFieldName1 => $dummyFieldValue1,
            $dummyFieldName2 => $dummyFieldValue2
        ], $productAId);

        $searchDocumentB = $this->createSearchDocument([
            $dummyFieldName1 => $dummyFieldValue1,
            $dummyFieldName2 => $dummyFieldValue2
        ], $productBId);

        $searchDocumentC = $this->createSearchDocument([$dummyFieldName1 => $dummyFieldValue1], $productCId);

        $this->stubSearchDocumentCollection->method('getDocuments')
            ->willReturn([$searchDocumentA, $searchDocumentB, $searchDocumentC]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $criterion1 = SearchCriterion::create($dummyFieldName1, $dummyFieldValue1, '=');
        $criterion2 = SearchCriterion::create($dummyFieldName2, $dummyFieldValue2, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::AND_CONDITION, [$criterion1, $criterion2]);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($mockCriteria, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testSearchDocumentsWithSameProductIdOverwritesEachOther()
    {
        $productId = ProductId::fromString('id');
        $dummyFieldName = 'foo';
        $dummyFieldValue = 'bar';

        $searchDocumentA = $this->createSearchDocument([$dummyFieldName => $dummyFieldValue], $productId);
        $searchDocumentB = $this->createSearchDocument([$dummyFieldName => $dummyFieldValue], $productId);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productId]);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();

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
     * @param ProductId[] $productIds
     */
    private function assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds(
        SearchDocumentCollection $collection,
        array $productIds
    ) {
        $this->assertCount(count($productIds), $collection, 'Failed asserting collection size matches expectation.');
        foreach ($productIds as $productId) {
            if (!$this->searchDocumentsCollectionContainsDocumentForProductId($collection, $productId)) {
                $this->fail(
                    sprintf('Failed asserting document for product ID "%s" is present in collection', $productId)
                );
            }
        }
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function searchDocumentsCollectionContainsDocumentForProductId(
        SearchDocumentCollection $collection,
        ProductId $productId
    ) {
        $documents = $collection->getDocuments();
        foreach ($documents as $document) {
            if ($document->getProductId() == $productId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $condition
     * @param \PHPUnit_Framework_MockObject_MockObject[] $mockCriteriaToReturn
     * @return SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCriteria($condition, array $mockCriteriaToReturn)
    {
        $mockCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockCriteria->method('hasAndCondition')->willReturn(SearchCriteria::AND_CONDITION === $condition);
        $mockCriteria->method('hasOrCondition')->willReturn(SearchCriteria::OR_CONDITION === $condition);
        $mockCriteria->method('getCriteria')->willReturn($mockCriteriaToReturn);

        return $mockCriteria;
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
}
