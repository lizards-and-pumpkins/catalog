<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Context\WebsiteContextDecorator;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
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

    /**
     * @return SearchEngine
     */
    protected function getSearchEngine()
    {
        return $this->searchEngine;
    }
    
    protected function getTestContext()
    {
        return $this->testContext;
    }

    /**
     * @param string[] $fields
     * @param ProductId $productId
     * @return SearchDocument
     */
    protected function createSearchDocument(array $fields, ProductId $productId)
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
        $this->assertCount(
            count($productIds),
            $collection,
            'Failed asserting that the search document collection size matches the number of expected product ids.'
        );
        foreach ($productIds as $productId) {
            if (!$this->isDocumentForProductIdInDocumentCollection($collection, $productId)) {
                $this->fail(sprintf(
                    'Failed asserting document for product ID "%s" is present in search document collection.',
                    $productId
                ));
            }
        }
    }

    /**
     * @param SearchDocumentCollection $collection
     * @param ProductId $productId
     * @return bool
     */
    private function isDocumentForProductIdInDocumentCollection(
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
     * @param string[] $contextDataSet
     * @return Context
     */
    private function createContextFromDataParts(array $contextDataSet)
    {
        $dataVersion = DataVersion::fromVersionString('-1');
        $contextBuilder = new ContextBuilder($dataVersion);

        return $contextBuilder->createContextsFromDataSets([$contextDataSet])[0];
    }

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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocument]));
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

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId, $productBId]);
    }

    public function testEmptyCollectionIsReturnedIfNoSearchDocumentsMatchesGivenCriteria()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturn(false);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertEmpty($result);
    }

    public function testCollectionContainsOnlySearchDocumentsMatchingGivenCriteria()
    {
        $productAId = ProductId::fromString('A');
        $productBId = ProductId::fromString('B');

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productAId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productBId);

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $matchingSearchDocumentField = SearchDocumentField::fromKeyAndValue('foo', 'bar');

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturnCallback(
            function (SearchDocument $searchDocument) use ($matchingSearchDocumentField) {
                return in_array($matchingSearchDocumentField, $searchDocument->getFieldsCollection()->getFields());
            }
        );

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productAId]);
    }

    public function testIfMultipleMatchingDocumentsHasSameProductIdOnlyOneInstanceIsReturned()
    {
        $productId = ProductId::fromString('A');

        $searchDocumentA = $this->createSearchDocument(['foo' => 'bar'], $productId);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], $productId);

        $this->stubSearchDocumentCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$searchDocumentA, $searchDocumentB]));
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);
        $stubCriteria->method('matches')->willReturn(true);

        $result = $this->searchEngine->getSearchDocumentsMatchingCriteria($stubCriteria, $this->testContext);

        $this->assertSearchDocumentCollectionContainsOnlyDocumentsForProductIds($result, [$productId]);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();
}
