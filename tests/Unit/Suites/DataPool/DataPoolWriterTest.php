<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubKeyValueStore;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubSearchEngine;
use LizardsAndPumpkins\DataPool\Stub\ClearableStubUrlKeyStore;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContext;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Util\Storage\Clearable;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
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

    public function testSnippetsIsWrittenToDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $stubSnippet = $this->getMockSnippet($testKey, $testContent);

        $this->getMockKeyValueStore()->expects($this->once())->method('set')->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippets($stubSnippet);
    }

    public function testSearchDocumentCollectionIsWrittenToDataPool()
    {
        /** @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocument */
        $stubSearchDocument = $this->createMock(SearchDocument::class);
        $this->getMockSearchEngine()->expects($this->once())->method('addDocument')->with($stubSearchDocument);

        $this->dataPoolWriter->writeSearchDocument($stubSearchDocument);
    }

    private function getMockSnippet(string $mockSnippetKey, string $mockSnippetContent) : Snippet
    {
        $mockSnippet = $this->createMock(Snippet::class);
        $mockSnippet->expects($this->once())->method('getKey')->willReturn($mockSnippetKey);
        $mockSnippet->expects($this->once())->method('getContent')->willReturn($mockSnippetContent);

        return $mockSnippet;
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->dataPoolWriter);
    }

    public function testItDelegatesClearToStorage()
    {
        /** @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject $mockSearchEngine */
        $mockSearchEngine = $this->createMock(ClearableStubSearchEngine::class);
        $mockSearchEngine->expects($this->once())->method('clear');

        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $mockKeyValueStore */
        $mockKeyValueStore = $this->createMock(ClearableStubKeyValueStore::class);
        $mockKeyValueStore->expects($this->once())->method('clear');

        /** @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject $mockUrlKeyStorage */
        $mockUrlKeyStorage = $this->createMock(ClearableStubUrlKeyStore::class);
        $mockUrlKeyStorage->expects($this->once())->method('clear');

        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine, $mockUrlKeyStorage);
        $writer->clear();
    }

    public function testItDelegatesStoreUrlKeys()
    {
        /** @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject $mockSearchEngine */
        $mockSearchEngine = $this->createMock(ClearableStubSearchEngine::class);

        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $mockKeyValueStore */
        $mockKeyValueStore = $this->createMock(ClearableStubKeyValueStore::class);

        /** @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject $mockUrlKeyStorage */
        $mockUrlKeyStorage = $this->createMock(ClearableStubUrlKeyStore::class);
        $mockUrlKeyStorage->expects($this->once())->method('addUrlKeyForVersion');

        $stubUrlKeysForContextsCollection = $this->createMock(UrlKeyForContextCollection::class);
        $stubUrlKeysForContextsCollection->method('getUrlKeys')->willReturn(
            [$this->createMock(UrlKeyForContext::class)]
        );

        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine, $mockUrlKeyStorage);
        $writer->writeUrlKeyCollection($stubUrlKeysForContextsCollection);
    }
}
