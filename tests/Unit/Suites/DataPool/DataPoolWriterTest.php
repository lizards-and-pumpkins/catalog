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
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $this->getMockSearchEngine()->expects($this->once())->method('addDocument')->with($stubSearchDocument);

        $this->dataPoolWriter->writeSearchDocument($stubSearchDocument);
    }

    /**
     * @param string $mockSnippetKey
     * @param string $mockSnippetContent
     * @return Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSnippet($mockSnippetKey, $mockSnippetContent)
    {
        $mockSnippet = $this->getMock(Snippet::class, [], [], '', false);
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
        $mockSearchEngine = $this->getMock(ClearableStubSearchEngine::class);
        $mockSearchEngine->expects($this->once())->method('clear');

        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $mockKeyValueStore */
        $mockKeyValueStore = $this->getMock(ClearableStubKeyValueStore::class);
        $mockKeyValueStore->expects($this->once())->method('clear');

        /** @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject $mockUrlKeyStorage */
        $mockUrlKeyStorage = $this->getMock(ClearableStubUrlKeyStore::class);
        $mockUrlKeyStorage->expects($this->once())->method('clear');

        $writer = new DataPoolWriter($mockKeyValueStore, $mockSearchEngine, $mockUrlKeyStorage);
        $writer->clear();
    }

    public function testItDelegatesStoreUrlKeys()
    {
        /** @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject $mockSearchEngine */
        $mockSearchEngine = $this->getMock(ClearableStubSearchEngine::class);

        /** @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject $mockKeyValueStore */
        $mockKeyValueStore = $this->getMock(ClearableStubKeyValueStore::class);

        /** @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject $mockUrlKeyStorage */
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
