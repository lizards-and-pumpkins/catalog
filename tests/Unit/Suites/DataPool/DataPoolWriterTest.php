<?php

namespace Brera\DataPool;

use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\SnippetResult;
use Brera\SnippetResultList;

/**
 * @covers \Brera\DataPool\DataPoolWriter
 * @uses Brera\Product\ProductId
 * @uses Brera\Http\HttpUrl
 */
class DataPoolWriterTest extends AbstractDataPoolTest
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    protected function setUp()
    {
        parent::setUp();

        $this->dataPoolWriter = new DataPoolWriter($this->stubKeyValueStore, $this->stubSearchEngine);
    }

    /**
     * @test
     */
    public function itShouldWriteASnippetListToTheDataPool()
    {
        $testContent = 'test-content';
        $testKey = 'test-key';

        $mockSnippetResult = $this->getMockBuilder(SnippetResult::class)
        ->disableOriginalConstructor()
        ->getMock();
        $mockSnippetResult->expects($this->once())->method('getContent')
        ->willReturn($testContent);
        $mockSnippetResult->expects($this->once())->method('getKey')
        ->willReturn($testKey);

        $mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $mockSnippetResultList->expects($this->once())
        ->method('getIterator')
        ->willReturn(new \ArrayIterator([$mockSnippetResult]));

        $this->stubKeyValueStore->expects($this->once())
        ->method('set')
        ->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippetResultList($mockSnippetResultList);
    }

    /**
     * @test
     */
    public function itShouldWriteSearchDocumentCollectionToTheDataPool()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class);

        $this->stubSearchEngine->expects($this->once())
            ->method('addSearchDocumentCollection')
            ->with($stubSearchDocumentCollection);

        $this->dataPoolWriter->writeSearchDocumentCollection($stubSearchDocumentCollection);
    }
}
