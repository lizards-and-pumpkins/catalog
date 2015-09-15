<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubKeyValueStore;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubSearchEngine;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Projection\UrlKeyForContext;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\Utils\Clearable;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
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

        $this->dataPoolWriter = new DataPoolWriter(
            $this->getMockKeyValueStore(),
            $this->getMockSearchEngine(),
            $this->getMockUrlKeyStore()
        );
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

        $this->getMockKeyValueStore()->expects($this->once())
            ->method('set')
            ->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippetList($mockSnippetList);
    }

    public function testSearchDocumentCollectionIsWrittenToDataPool()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class);

        $this->getMockSearchEngine()->expects($this->once())
            ->method('addSearchDocumentCollection')
            ->with($stubSearchDocumentCollection);

        $this->dataPoolWriter->writeSearchDocumentCollection($stubSearchDocumentCollection);
    }

    public function testSnippetIsWrittenToDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $mockSnippet = $this->getMockSnippet($testKey, $testContent);

        $this->getMockKeyValueStore()->expects($this->once())
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
        $mockUrlKeyStorage = $this->getMock(ClearableStubUrlKeyStore::class);
        $mockKeyValueStore->expects($this->once())->method('clear');
        $mockSearchEngine->expects($this->once())->method('clear');
        $mockUrlKeyStorage->expects($this->once())->method('clear');
        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine, $mockUrlKeyStorage);
        $writer->clear();
    }

    public function testItDelegatesStoreUrlKeys()
    {
        $mockSearchEngine = $this->getMock(ClearableStubSearchEngine::class);
        $mockKeyValueStore = $this->getMock(ClearableStubKeyValueStore::class);
        $mockUrlKeyStorage = $this->getMock(ClearableStubUrlKeyStore::class);
        $mockUrlKeyStorage->expects($this->once())->method('addUrlKeyForVersion');

        $stubUrlKeysForContextsCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        $stubUrlKeysForContextsCollection->method('getUrlKeys')->willReturn(
            [$this->getMock(UrlKeyForContext::class, [], [], '', false)]
        );

        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine, $mockUrlKeyStorage);
        $writer->writeUrlKeyCollection($stubUrlKeysForContextsCollection);
    }
}
