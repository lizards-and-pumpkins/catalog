<?php

namespace Brera\DataPool;

use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStoreTest;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\Stub\ClearableStubKeyValueStore;
use Brera\DataPool\Stub\ClearableStubSearchEngine;
use Brera\Snippet;
use Brera\SnippetList;
use Brera\Utils\Clearable;

/**
 * @covers \Brera\DataPool\DataPoolWriter
 * @uses   Brera\Product\ProductId
 * @uses   Brera\Http\HttpUrl
 */
class DataPoolWriterTest extends AbstractDataPoolTest
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    protected function setUp()
    {
        /* TODO: Refactor */
        parent::setUp();

        $this->dataPoolWriter = new DataPoolWriter($this->getStubKeyValueStore(), $this->getStubSearchEngine());
    }

    public function testSnippetListIsWrittenToDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $mockSnippet = $this->getMockSnippet($testKey, $testContent);

        $mockSnippetList = $this->getMock(SnippetList::class);
        $mockSnippetList->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$mockSnippet]));

        $this->getStubKeyValueStore()->expects($this->once())
            ->method('set')
            ->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippetList($mockSnippetList);
    }

    public function testSearchDocumentCollectionIsWrittenToDataPool()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class);

        $this->getStubSearchEngine()->expects($this->once())
            ->method('addSearchDocumentCollection')
            ->with($stubSearchDocumentCollection);

        $this->dataPoolWriter->writeSearchDocumentCollection($stubSearchDocumentCollection);
    }

    public function testSnippetIsWrittenToDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $mockSnippet = $this->getMockSnippet($testKey, $testContent);

        $this->getStubKeyValueStore()->expects($this->once())
            ->method('set')
            ->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippet($mockSnippet);

    }

    /**
     * @param string $mockSnippetKey
     * @param string $mockSnippetContent
     * @return Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSnippet($mockSnippetKey, $mockSnippetContent)
    {
        $mockSnippet = $this->getMock(Snippet::class, [], [], '', false);
        $mockSnippet->expects($this->once())
            ->method('getKey')
            ->willReturn($mockSnippetKey);
        $mockSnippet->expects($this->once())
            ->method('getContent')
            ->willReturn($mockSnippetContent);

        return $mockSnippet;
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->dataPoolWriter);
    }

    public function testItDelegatesClearToStorage()
    {
        $mockSearchEngine = $this->getMock(ClearableStubSearchEngine::class);
        $mockKeyValueStore = $this->getMock(ClearableStubKeyValueStore::class);
        $mockKeyValueStore->expects($this->once())->method('clear');
        $mockSearchEngine->expects($this->once())->method('clear');
        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine);
        $writer->clear();
    }
}
