<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

/**
 * @covers \Brera\DataPool\SearchEngine\InMemorySearchEngine
 */
class InMemorySearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemorySearchEngine
     */
    private $searchEngine;

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

    protected function setUp()
    {
        $this->stubSearchDocument = $this->getMockBuilder(SearchDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubSearchDocument2 = $this->getMockBuilder(SearchDocument::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubSearchDocumentCollection = $this->getMockBuilder(SearchDocumentCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubContext = $this->getMock(Context::class);

        $this->searchEngine = new InMemorySearchEngine();
    }

    /**
     * @test
     */
    public function itShouldImplementSearchEngineInterface()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayWhateverIsAskedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayIfQueryStringIsNotFoundInIndex()
    {
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, null
        );

        $this->searchEngine->addSearchDocument($this->stubSearchDocument);

        $result = $this->searchEngine->query('baz', $this->stubContext);

        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function itShouldAddEntryIntoIndexAndThenFindIt()
    {
        $searchDocumentContent = 'qux';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocumentContent
        );

        $this->searchEngine->addSearchDocument($this->stubSearchDocument);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    /**
     * @test
     */
    public function itShouldAddMultipleEntriesToIndex()
    {
        $searchDocument1Content = 'content1';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocument1Content
        );

        $searchDocument2Content = 'content2';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2, $this->stubContext, $stubFieldsCollection, $searchDocument2Content
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocument1Content, $searchDocument2Content], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnOnlyEntriesContainingRequestedString()
    {
        $searchDocumentContent = 'content';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocumentContent
        );

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'quz']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2, $this->stubContext, $stubFieldsCollection, null
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnOnlyMatchesWithMatchingContexts()
    {
        $searchDocumentContent = 'content';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocumentContent
        );

        $stubContext2 = $this->getMock(Context::class);
        $stubContext2->expects($this->never())
            ->method('someDummyExpectationToMakeObjectDifferent');
        $this->prepareStubSearchDocument($this->stubSearchDocument2, $stubContext2, null, null);

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnEntriesContainingRequestedString()
    {
        $searchDocument1Content = 'content1';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'barbarism']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocument1Content
        );

        $searchDocument2Content = 'content2';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'cabaret']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2, $this->stubContext, $stubFieldsCollection, $searchDocument2Content
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocument1Content, $searchDocument2Content], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnUniqueEntries()
    {
        $searchDocumentContent = 'content';

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'barbarism']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument, $this->stubContext, $stubFieldsCollection, $searchDocumentContent
        );

        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'cabaret']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument2, $this->stubContext, $stubFieldsCollection, $searchDocumentContent
        );

        $this->stubSearchDocumentCollection->expects($this->any())
            ->method('getDocuments')
            ->willReturn([$this->stubSearchDocument, $this->stubSearchDocument2]);

        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

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
    )
    {
        $stubSearchDocument->expects($this->once())
            ->method('getContext')
            ->willReturn($stubContext);

        if (!is_null($stubSearchDocumentFieldCollection)) {
            $stubSearchDocument->expects($this->once())
                ->method('getFieldsCollection')
                ->willReturn($stubSearchDocumentFieldCollection);
        }

        if (!is_null($content)) {
            $stubSearchDocument->expects($this->any())
                ->method('getContent')
                ->willReturn($content);
        }
    }
}
