<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocument;

    /**
     * @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocument2;

    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    protected function setUp()
    {
        // todo:
        // Do not use SearchDocument mocks because the search engine implementation may discard
        // document instances for storage and thus any expectations set would always fail.
        // @see itShouldReturnAnArrayWithOneProductIdWithMatchingCriteria() for an example.
        $this->stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->stubSearchDocument2 = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->stubContext = $this->getMock(Context::class);
        $this->stubContext->expects($this->any())->method('getSupportedCodes')->willReturn(['dummy-part']);
        $this->stubContext->expects($this->any())->method('supportsCode')->with('dummy-part')->willReturn(true);
        $this->stubContext->expects($this->any())->method('getValue')->with('dummy-part')->willReturn('dummy-value');

        $this->searchEngine = $this->createSearchEngineInstance();
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testEmptyArrayIsReturnedRegardlessOfWhatHasBeenQueriedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar', $this->stubContext);
        $this->assertCount(0, $result);
    }

    public function testEntryIsAddedIntoIndexAndThenFound()
    {
        $searchDocumentContent = 'qux';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocumentContent
        );

        $this->searchEngine->addSearchDocument($this->stubSearchDocument);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testEmptyArrayIsReturnedIfQueryStringIsNotFoundInIndex()
    {
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            null
        );

        $this->searchEngine->addSearchDocument($this->stubSearchDocument);

        $result = $this->searchEngine->query('baz', $this->stubContext);

        $this->assertCount(0, $result);
    }

    public function testMultipleEntriesAreAddedToIndex()
    {
        $keyword = 'bar';

        $searchDocument1Content = 'content1';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => $keyword]);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocument1Content
        );

        $searchDocument2Content = 'content2';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => $keyword]);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocument2Content
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query($keyword, $this->stubContext);

        $this->assertEmpty(array_diff([$searchDocument1Content, $searchDocument2Content], $result));
    }

    public function testOnlyEntriesContainingRequestedStringAreReturned()
    {
        $searchDocumentContent = 'content';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocumentContent
        );

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'quz']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $this->stubContext,
            $stubFieldsCollection,
            null
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $contextPartCode = 'dummy-part';
        $stubDocument1Context = $this->getMock(Context::class);
        $stubDocument1Context->expects($this->any())->method('getValue')->willReturn('value-1');
        $stubDocument1Context->expects($this->any())->method('supportsCode')->willReturn(true);

        $stubDocument2Context = $this->getMock(Context::class);
        $stubDocument2Context->expects($this->any())->method('getValue')->willReturn('value-2');
        $stubDocument2Context->expects($this->any())->method('supportsCode')->willReturn(true);

        $stubQueryContext = $this->getMock(Context::class);
        $stubQueryContext->expects($this->any())->method('getSupportedCodes')->willReturn([$contextPartCode]);
        $stubQueryContext->expects($this->any())->method('getValue')->willReturn('value-2');

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $stubDocument1Context,
            $stubFieldsCollection,
            'content1'
        );
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $stubDocument2Context,
            $stubFieldsCollection,
            'content2'
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertEquals(['content2'], $result);
    }

    public function testPartialContextsAreMatched()
    {
        $stubDocument1Context = $this->getMock(Context::class);
        $stubDocument1Context->expects($this->any())->method('getValue')
            ->willReturnMap([
                ['part1', 'value1'],
                ['part2', 'value2'],
            ]);
        $stubDocument1Context->expects($this->any())->method('supportsCode')->willReturn(true);

        $stubDocument2Context = $this->getMock(Context::class);
        $stubDocument2Context->expects($this->any())->method('getValue')
            ->willReturnMap([
                ['part1', 'value1'],
                ['part2', 'value2'],
            ]);
        $stubDocument2Context->expects($this->any())->method('supportsCode')->willReturn(true);

        $stubQueryContext = $this->getMock(Context::class);
        $stubQueryContext->expects($this->any())->method('getSupportedCodes')->willReturn(['part2']);
        $stubQueryContext->expects($this->any())->method('getValue')->willReturn('value2');

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $stubDocument1Context,
            $stubFieldsCollection,
            'content1'
        );
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $stubDocument2Context,
            $stubFieldsCollection,
            'content2'
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertArraysHasEqualElements(['content1', 'content2'], $result);
    }

    public function testContextPartsThatAreNotInSearchDocumentContextAreIgnored()
    {
        $contextPartCode = 'dummy-part';
        $stubQueryContext = $this->getMock(Context::class);
        $stubQueryContext->expects($this->once())->method('getSupportedCodes')
            ->willReturn([$contextPartCode . '1', $contextPartCode . '2']);
        $stubQueryContext->expects($this->any())->method('getValue')
            ->willReturnMap([
                [$contextPartCode . '1', 'value1'],
                [$contextPartCode . '2', 'value2'],
            ]);

        $stubDocumentContext = $this->getMock(Context::class);
        $stubDocumentContext->expects($this->any())->method('supportsCode')
            ->willReturnMap([
                [$contextPartCode . '1', false],
                [$contextPartCode . '2', true],
            ]);
        $stubDocumentContext->expects($this->any())->method('getValue')
            ->with($contextPartCode . '2')->willReturn('value2');

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $stubDocumentContext,
            $stubFieldsCollection,
            'content'
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertEquals(['content'], $result);
    }

    public function testNoMatchesReturnedIfNoContextPartIsSupported()
    {
        $contextPartCode = 'dummy-part';
        $stubQueryContext = $this->getMock(Context::class);
        $stubQueryContext->expects($this->any())->method('getSupportedCodes')->willReturn([$contextPartCode]);

        $stubDocumentContext = $this->getMock(Context::class);
        $stubDocumentContext->expects($this->any())->method('supportsCode')->with($contextPartCode)->willReturn(false);
        $stubDocumentContext->expects($this->never())->method('getValue');

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $stubDocumentContext,
            $stubFieldsCollection,
            'content'
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertEquals([], $result, 'Expected no search results.');
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $searchDocument1Content = 'content1';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'barbarism']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocument1Content
        );

        $searchDocument2Content = 'content2';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'cabaret']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocument2Content
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEmpty(array_diff([$searchDocument1Content, $searchDocument2Content], $result));
    }

    public function testUniqueEntriesAreReturned()
    {
        $searchDocumentContent = 'content';

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'barbarism']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocumentContent
        );

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'cabaret']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocumentContent
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testEmptyArrayIsReturnedForEmptySearchCriteria()
    {
        $this->assertSame([], $this->searchEngine->queryGivenFields([], $this->stubContext));
    }

    public function testExceptionIsThrownIfFieldNameIsInteger()
    {
        $this->setExpectedException(InvalidFieldIdentifierException::class);
        $integerFieldName = 1;
        $this->searchEngine->queryGivenFields([$integerFieldName => 'dummy-search-term'], $this->stubContext);
    }

    public function testEmptyArrayIsReturnedIfNoMatchesAreFound()
    {
        $this->assertSame([], $this->searchEngine->queryGivenFields(
            ['test-field' => 'test-search-term'],
            $this->stubContext
        ));
    }

    public function testArrayWithOneProductIdMatchingCriteriaIsReturned()
    {
        $testProductId = 'id10';
        $testFieldName = 'test-field-name';
        $testQueryTerm = 'test-query-term';

        $testFieldCollection = SearchDocumentFieldCollection::fromArray([$testFieldName => $testQueryTerm]);
        // Do not search document mock because search engine implementation may discard instance
        // for storage and thus any expectations set always fail.
        $testSearchDocument = new SearchDocument($testFieldCollection, $this->stubContext, $testProductId);
        $searchEngine = $this->createSearchEngineInstance();
        $searchEngine->addSearchDocument($testSearchDocument);
        
        $result = $searchEngine->queryGivenFields([$testFieldName => $testQueryTerm], $this->stubContext);
        $this->assertEquals([$testProductId], $result);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();

    /**
     * @param string[] $fieldsMap
     * @return SearchDocumentFieldCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentFieldCollectionFromArray(array $fieldsMap)
    {
        $stubSearchDocumentFieldArray = [];

        foreach ($fieldsMap as $key => $value) {
            $stubSearchDocumentField = $this->getMockBuilder(SearchDocumentField::class)
                ->disableOriginalConstructor()
                ->getMock();
            $stubSearchDocumentField->expects($this->any())
                ->method('getKey')
                ->willReturn($key);
            $stubSearchDocumentField->expects($this->any())
                ->method('getValue')
                ->willReturn($value);

            array_push($stubSearchDocumentFieldArray, $stubSearchDocumentField);
        }

        $stubSearchDocumentFieldCollection = $this->getMockBuilder(SearchDocumentFieldCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubSearchDocumentFieldCollection->expects($this->any())
            ->method('getFields')
            ->willReturn($stubSearchDocumentFieldArray);

        return $stubSearchDocumentFieldCollection;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $stubSearchDocument
     * @param \PHPUnit_Framework_MockObject_MockObject $stubContext
     * @param \PHPUnit_Framework_MockObject_MockObject|null $stubSearchDocumentFieldCollection
     * @param mixed $content
     */
    private function prepareStubSearchDocument(
        \PHPUnit_Framework_MockObject_MockObject $stubSearchDocument,
        \PHPUnit_Framework_MockObject_MockObject $stubContext,
        \PHPUnit_Framework_MockObject_MockObject $stubSearchDocumentFieldCollection = null,
        $content = null
    ) {
        $stubSearchDocument->expects($this->any())
            ->method('getContext')
            ->willReturn($stubContext);

        $stubSearchDocument->expects($this->any())
            ->method('getFieldsCollection')
            ->willReturn($stubSearchDocumentFieldCollection);

        $stubSearchDocument->expects($this->any())
            ->method('getContent')
            ->willReturn($content);
    }

    /**
     * @param mixed[] $array1
     * @param mixed[] $array2
     */
    private function assertArraysHasEqualElements(array $array1, array $array2)
    {
        $this->assertEmpty(array_diff($array1, $array2));
    }
}
