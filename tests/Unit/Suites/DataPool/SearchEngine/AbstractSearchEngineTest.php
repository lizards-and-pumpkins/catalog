<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubSearchDocument;

    /**
     * @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubSearchDocument2;

    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubSearchDocumentCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stubContext;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

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
        $this->stubContext->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn([]);

        $this->searchEngine = $this->createSearchEngineInstance();
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
    public function itShouldReturnAnEmptyArrayWhateverIsQueriedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar', $this->stubContext);

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
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocumentContent
        );

        $this->searchEngine->addSearchDocument($this->stubSearchDocument);

        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnEmptyArrayIfQueryStringIsNotFoundInIndex()
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

    /**
     * @test
     */
    public function itShouldAddMultipleEntriesToIndex()
    {
        $searchDocument1Content = 'content1';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['foo' => 'bar']);
        $this->prepareStubSearchDocument(
            $this->stubSearchDocument,
            $this->stubContext,
            $stubFieldsCollection,
            $searchDocument1Content
        );

        $searchDocument2Content = 'content2';
        $stubFieldsCollection = $this->createStubSearchDocumentFieldCollectionFromArray(['baz' => 'bar']);
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

    /**
     * @test
     */
    public function itShouldReturnOnlyEntriesContainingRequestedString()
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

    /**
     * @test
     */
    public function itShouldReturnOnlyMatchesWithMatchingContexts()
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

    /**
     * @test
     */
    public function itShouldMatchPartialContexts()
    {
        $stubDocument1Context = $this->getMock(Context::class);
        $stubDocument1Context->expects($this->any())->method('getValue')
            ->willReturnMap([
                ['part1', 'value1'],
                ['part2', 'value2'],
            ]);

        $stubDocument2Context = $this->getMock(Context::class);
        $stubDocument2Context->expects($this->any())->method('getValue')
            ->willReturnMap([
                ['part1', 'value1'],
                ['part2', 'value2'],
            ]);

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

        $this->assertEquals(['content1', 'content2'], $result);
    }

    /**
     * @test
     */
    public function itShouldIgnoreContextPartsFromTheQueryThatAreNotInTheSearchDocumentContext()
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

        $this->assertEquals(['content'], $result);
    }

    /**
     * @test
     */
    public function itShouldReturnEntriesContainingRequestedString()
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

    /**
     * @test
     */
    public function itShouldReturnUniqueEntries()
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
}
