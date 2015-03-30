<?php

namespace Brera\DataPool;

use Brera\Context\Context;

/**
 * @covers \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Product\PoCSku
 */
class DataPoolReaderTest extends AbstractDataPoolTest
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    protected function setUp()
    {
        parent::setUp();

        $this->dataPoolReader = new DataPoolReader($this->stubKeyValueStore, $this->stubSearchEngine);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetIfItExists()
    {
        $testValue = '<p>html</p>';
        $testKey = 'test';

        $this->addGetMethodToStubKeyValueStore($testValue);

        $this->assertEquals($testValue, $this->dataPoolReader->getSnippet($testKey));
    }

    /**
     * @test
     *
     * @dataProvider snippetListProvider
     */
    public function itShouldReturnASnippetList($keyValueStorageReturn, $expectedList)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);

        $this->assertEquals(
            $expectedList,
            $this->dataPoolReader->getChildSnippetKeys('some_key')
        );
    }

    /**
     * @return mixed[]
     */
    public function snippetListProvider()
    {
        return [
            [json_encode(false), []],
            ['[]', []],
            ['{}', []],
            [json_encode(['test_key1', 'test_key2', 'some_key']), ['test_key1', 'test_key2', 'some_key']],
        ];
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenJsonProvider
     */
    public function itShouldThrowAnExceptionOnBrokenJSON($brokenJson)
    {
        $this->addGetMethodToStubKeyValueStore($brokenJson);
        $this->dataPoolReader->getChildSnippetKeys('some_key');
    }

    /**
     * @return mixed[]
     */
    public function brokenJsonProvider()
    {
        return [
            [new \stdClass()],
            [[]],
            ['test'],
            [123],
            [123.23]
        ];
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider invalidKeyProvider
     */
    public function itShouldOnlyAcceptStringKeyForSnippetList($key)
    {
        $this->dataPoolReader->getChildSnippetKeys($key);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider invalidKeyProvider
     */
    public function itShouldOnlyAcceptStringKeysForGetSnippet($key)
    {
        $this->dataPoolReader->getSnippet($key);
    }

    /**
     * @return mixed[]
     */
    public function invalidKeyProvider()
    {
        return [
            [new \stdClass()],
            [123],
            [123.23],
            [[]],
        ];

    }

    /**
     * @return mixed[]
     */
    public function brokenKeysForSnippetsProvider()
    {
        return [
            [new \stdClass()],
            [123],
            [123.23],
            ['string'],
        ];
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenKeysForSnippetsProvider
     */
    public function itShouldOnlyAcceptStringKeysForGetSnippets($key)
    {
        $this->dataPoolReader->getSnippets($key);
    }

    /**
     * @test
     */
    public function itShouldReturnSnippets()
    {
        $keyValueStorageReturn = [
            'key' => 'value',
            'key2' => 'value2',
        ];
        $this->addMultiGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $snippets = $this->dataPoolReader->getSnippets(['key', 'key2']);

        $this->assertEquals($keyValueStorageReturn, $snippets);
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfASnippetKeyIsNotInTheStore()
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(false);
        $this->assertFalse($this->dataPoolReader->hasSnippet('test'));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfASnippetKeyIsInTheStore()
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(true);
        $this->assertTrue($this->dataPoolReader->hasSnippet('test'));
    }

    /**
     * @test
     */
    public function itShouldReturnNegativeOneIfTheCurrentVersionIsNotSet()
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('has')
            ->with('current_version')
            ->willReturn(false);
        $this->assertSame('-1', $this->dataPoolReader->getCurrentDataVersion());
    }

    /**
     * @test
     */
    public function itShouldReturnTheCurrentVersion()
    {
        $this->stubKeyValueStore->expects($this->once())
            ->method('has')
            ->with('current_version')
            ->willReturn(true);
        $this->stubKeyValueStore->expects($this->once())
            ->method('get')
            ->with('current_version')
            ->willReturn('123');
        $this->assertSame('123', $this->dataPoolReader->getCurrentDataVersion());
    }

    /**
     * @test
     */
    public function itShouldGetSearchResultsFromSearchEngine()
    {
        $stubContext = $this->getMock(Context::class);

        $this->stubSearchEngine->expects($this->once())
            ->method('query');

        $this->dataPoolReader->getSearchResults('foo', $stubContext);
    }
}
