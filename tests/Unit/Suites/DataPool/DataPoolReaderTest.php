<?php

namespace Brera\DataPool;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;

/**
 * @covers \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Product\SampleSku
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

        $this->dataPoolReader = new DataPoolReader($this->getStubKeyValueStore(), $this->getStubSearchEngine());
    }

    public function testSnippetIsReturnedIfExists()
    {
        $testValue = '<p>html</p>';
        $testKey = 'test';

        $this->addGetMethodToStubKeyValueStore($testValue);

        $this->assertEquals($testValue, $this->dataPoolReader->getSnippet($testKey));
    }

    /**
     * @dataProvider snippetListProvider
     */
    public function testSnippetListIsReturned($keyValueStorageReturn, $expectedList)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);

        $this->assertEquals(
            $expectedList,
            $this->dataPoolReader->getChildSnippetKeys('some_key')
        );
    }

    /**
     * @return array[]
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
     * @dataProvider brokenJsonProvider
     */
    public function testExceptionIsThrownIfJsonIsBroken($brokenJson)
    {
        $this->setExpectedException(\RuntimeException::class);
        $this->addGetMethodToStubKeyValueStore($brokenJson);
        $this->dataPoolReader->getChildSnippetKeys('some_key');
    }

    /**
     * @return array[]
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
     * @dataProvider invalidKeyProvider
     */
    public function testOnlyStringKeyIsAcceptedForSnippetList($key)
    {
        $this->setExpectedException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getChildSnippetKeys($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     */
    public function testOnlyStringKeysAreAcceptedForGetSnippet($key)
    {
        $this->setExpectedException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getSnippet($key);
    }

    /**
     * @return array[]
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
     * @dataProvider brokenKeysForSnippetsProvider
     */
    public function testOnlyStringKeysAreAcceptedForGetSnippets($key)
    {
        $this->setExpectedException(\RuntimeException::class);
        $this->dataPoolReader->getSnippets($key);
    }

    /**
     * @return array[]
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

    public function testExceptionIsThrownIfTheKeyIsEmpty()
    {
        $this->setExpectedException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getSnippet('');
    }

    public function testSnippetsAreReturned()
    {
        $keyValueStorageReturn = [
            'key' => 'value',
            'key2' => 'value2',
        ];
        $this->addMultiGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $snippets = $this->dataPoolReader->getSnippets(['key', 'key2']);

        $this->assertEquals($keyValueStorageReturn, $snippets);
    }

    public function testFalseIsReturnedIfASnippetKeyIsNotInTheStore()
    {
        $this->getStubKeyValueStore()->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(false);
        $this->assertFalse($this->dataPoolReader->hasSnippet('test'));
    }

    public function testTrueIsReturnedIfASnippetKeyIsInTheStore()
    {
        $this->getStubKeyValueStore()->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(true);
        $this->assertTrue($this->dataPoolReader->hasSnippet('test'));
    }

    public function testNegativeOneIsReturnedIfTheCurrentVersionIsNotSet()
    {
        $this->getStubKeyValueStore()->expects($this->once())
            ->method('has')
            ->with('current_version')
            ->willReturn(false);
        $this->assertSame('-1', $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testCurrentVersionIsReturned()
    {
        $currentDataVersion = '123';
        $this->getStubKeyValueStore()->expects($this->once())
            ->method('has')
            ->with('current_version')
            ->willReturn(true);
        $this->getStubKeyValueStore()->expects($this->once())
            ->method('get')
            ->with('current_version')
            ->willReturn($currentDataVersion);
        $this->assertSame($currentDataVersion, $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testSearchResultsAreReturnedFromSearchEngine()
    {
        $stubContext = $this->getMock(Context::class);

        $this->getStubSearchEngine()->expects($this->once())
            ->method('query');

        $this->dataPoolReader->getSearchResults('foo', $stubContext);
    }

    public function testCriteriaQueriesAreDelegatedToSearchEngine()
    {
        $mockCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);

        $this->getStubSearchEngine()->expects($this->once())
            ->method('getContentOfSearchDocumentsMatchingCriteria')
            ->with($mockCriteria, $stubContext);

        $this->dataPoolReader->getProductIdsMatchingCriteria($mockCriteria, $stubContext);
    }
}
