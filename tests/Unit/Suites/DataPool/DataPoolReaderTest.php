<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Import\Product\ProductId;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
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
     * @dataProvider snippetsProvider
     * @param string $keyValueStorageReturn
     * @param string[] $expectedContent
     */
    public function testSnippetIsReturned($keyValueStorageReturn, array $expectedContent)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $this->assertEquals($expectedContent, $this->dataPoolReader->getChildSnippetKeys('some_key'));
    }

    /**
     * @return array[]
     */
    public function snippetsProvider()
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
        $this->expectException(\RuntimeException::class);
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
    public function testOnlyStringKeyIsAcceptedForSnippets($key)
    {
        $this->expectException(InvalidKeyValueStoreKeyException::class);
        $this->dataPoolReader->getChildSnippetKeys($key);
    }

    /**
     * @dataProvider invalidKeyProvider
     * @param mixed $key
     */
    public function testOnlyStringKeysAreAcceptedForGetSnippet($key)
    {
        $this->expectException(InvalidKeyValueStoreKeyException::class);
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

    public function testExceptionIsThrownIfTheKeyIsEmpty()
    {
        $this->expectException(InvalidKeyValueStoreKeyException::class);
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
        /** @var QueryOptions|\PHPUnit_Framework_MockObject_MockObject $stubQueryOptions */
        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $mockCriteria */
        $mockCriteria = $this->getMock(SearchCriteria::class);

        $this->getMockSearchEngine()->expects($this->once())->method('query')->with($mockCriteria, $stubQueryOptions);

        $this->dataPoolReader->getSearchResultsMatchingCriteria($mockCriteria, $stubQueryOptions);
    }

    public function testFullTextQueriesAreDelegatedToSearchEngine()
    {
        /** @var QueryOptions|\PHPUnit_Framework_MockObject_MockObject $stubQueryOptions */
        $stubQueryOptions = $this->getMock(QueryOptions::class, [], [], '', false);

        $testQueryString = 'foo';

        $this->getMockSearchEngine()->expects($this->once())->method('queryFullText')
            ->with($testQueryString, $stubQueryOptions);

        $this->dataPoolReader->getSearchResultsMatchingString($testQueryString, $stubQueryOptions);
    }

    public function testItDelegatesUrlKeyReadsToUrlKeyStorage()
    {
        $expected = ['test.html'];
        $this->getMockUrlKeyStore()->expects($this->once())->method('getForDataVersion')->willReturn($expected);
        $this->assertSame($expected, $this->dataPoolReader->getUrlKeysForVersion('1.0'));
    }

    public function testItDelegatesQueriesForProductIdsToTheSearchEngine()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        
        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $stubSortBy */
        $stubSortBy = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $rowsPerPage = 1000;
        $pageNumber = 1;
        
        /** @var ProductId[]|\PHPUnit_Framework_MockObject_MockObject[] $matchingProductIds */
        $matchingProductIds = [$this->getMock(ProductId::class, [], [], '', false)];

        /** @var SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject $stubSearchResponse */
        $stubSearchResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchResponse->method('getProductIds')->willReturn($matchingProductIds);

        $this->getMockSearchEngine()->expects($this->once())
            ->method('query')->willReturn($stubSearchResponse);

        $result = $this->dataPoolReader->getProductIdsMatchingCriteria(
            $stubCriteria,
            $stubContext,
            $stubSortBy,
            $rowsPerPage,
            $pageNumber
        );
        $this->assertSame($matchingProductIds, $result);
    }

    public function testTheReturnedProductIdArrayIsNumericallyIndexed()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->getMock(SearchCriteria::class);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        
        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $stubSortBy */
        $stubSortBy = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $rowsPerPage = 1000;
        $pageNumber = 1;
        
        /** @var ProductId[]|\PHPUnit_Framework_MockObject_MockObject[] $matchingProductIds */
        $matchingProductIds = ['non-numeric-key' => $this->getMock(ProductId::class, [], [], '', false)];

        /** @var SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject $stubSearchResponse */
        $stubSearchResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchResponse->method('getProductIds')->willReturn($matchingProductIds);

        $this->getMockSearchEngine()->expects($this->once())
            ->method('query')->willReturn($stubSearchResponse);

        $result = $this->dataPoolReader->getProductIdsMatchingCriteria(
            $stubCriteria,
            $stubContext,
            $stubSortBy,
            $rowsPerPage,
            $pageNumber
        );
        $this->assertSame([0], array_keys($result));
    }
}
