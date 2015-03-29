<?php

namespace Brera\DataPool;

use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\SnippetResult;
use Brera\SnippetResultList;

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

    /**
     * @test
     */
    public function itShouldWriteASnippetListToTheDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $mockSnippetResult = $this->getMockSnippetResult($testKey, $testContent);

        $mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $mockSnippetResultList->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$mockSnippetResult]));

        $this->getStubKeyValueStore()->expects($this->once())
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

        $this->getStubSearchEngine()->expects($this->once())
            ->method('addSearchDocumentCollection')
            ->with($stubSearchDocumentCollection);

        $this->dataPoolWriter->writeSearchDocumentCollection($stubSearchDocumentCollection);
    }

    /**
     * @test
     */
    public function itShouldWriteSnippetResultIntoDataPool()
    {
        $testKey = 'test-key';
        $testContent = 'test-content';

        $mockSnippetResult = $this->getMockSnippetResult($testKey, $testContent);

        $this->getStubKeyValueStore()->expects($this->once())
            ->method('set')
            ->with($testKey, $testContent);

        $this->dataPoolWriter->writeSnippetResult($mockSnippetResult);

    }

    /**
     * @param string $mockSnippetKey
     * @param string $mockSnippetContent
     * @return SnippetResult|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSnippetResult($mockSnippetKey, $mockSnippetContent)
    {
        $mockSnippetResult = $this->getMock(SnippetResult::class, [], [], '', false);
        $mockSnippetResult->expects($this->once())
            ->method('getKey')
            ->willReturn($mockSnippetKey);
        $mockSnippetResult->expects($this->once())
            ->method('getContent')
            ->willReturn($mockSnippetContent);

        return $mockSnippetResult;
    }
}
