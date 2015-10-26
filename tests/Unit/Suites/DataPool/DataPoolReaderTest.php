<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
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

        $this->dataPoolReader = new DataPoolReader(
            $this->getMockKeyValueStore(),
            $this->getMockSearchEngine(),
            $this->getMockUrlKeyStore()
        );
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
     * @param string $keyValueStorageReturn
     * @param string[] $expectedList
     */
    public function testSnippetListIsReturned($keyValueStorageReturn, $expectedList)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $this->assertEquals($expectedList, $this->dataPoolReader->getChildSnippetKeys('some_key'));
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
     * @param mixed $brokenJson
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
     * @param mixed $key
     */
    public function testOnlyStringKeyIsAcceptedForSnippetList($key)
    {
        $this->setExpectedException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getChildSnippetKeys($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     * @param mixed $key
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
     * @param mixed $key
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
        $this->getMockKeyValueStore()->method('has')->with('test')->willReturn(false);
        $this->assertFalse($this->dataPoolReader->hasSnippet('test'));
    }

    public function testTrueIsReturnedIfASnippetKeyIsInTheStore()
    {
        $this->getMockKeyValueStore()->method('has')->with('test')->willReturn(true);
        $this->assertTrue($this->dataPoolReader->hasSnippet('test'));
    }

    public function testNegativeOneIsReturnedIfTheCurrentVersionIsNotSet()
    {
        $this->getMockKeyValueStore()->method('has')->with('current_version')->willReturn(false);
        $this->assertSame('-1', $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testCurrentVersionIsReturned()
    {
        $currentDataVersion = '123';
        $this->getMockKeyValueStore()->method('has')->with('current_version')->willReturn(true);
        $this->getMockKeyValueStore()->method('get')->with('current_version')->willReturn($currentDataVersion);

        $this->assertSame($currentDataVersion, $this->dataPoolReader->getCurrentDataVersion());
    }

    public function testCriteriaQueriesAreDelegatedToSearchEngine()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $mockCriteria */
        $mockCriteria = $this->getMock(SearchCriteria::class);

        $selectedFilters = [];

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->getMockSearchEngine()->expects($this->once())->method('getSearchDocumentsMatchingCriteria')
            ->with($mockCriteria, $selectedFilters, $stubContext);

        $facetFiltersConfig = [];
        $rowsPerPage = 100;
        $pageNumber = 0;
        $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $mockCriteria,
            $selectedFilters,
            $stubContext,
            $facetFiltersConfig,
            $rowsPerPage,
            $pageNumber
        );
    }

    public function testItDelegatesUrlKeyReadsToUrlKeyStorage()
    {
        $expected = ['test.html'];
        $this->getMockUrlKeyStore()->expects($this->once())->method('getForDataVersion')->willReturn($expected);
        $this->assertSame($expected, $this->dataPoolReader->getUrlKeysForVersion('1.0'));
    }
}
