<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Product\ProductId;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
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

        $this->getMockSearchEngine()->expects($this->once())->method('query')
            ->with($mockCriteria, $selectedFilters, $stubContext);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);;

        $rowsPerPage = 100;
        $pageNumber = 0;
        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $sortOrderConfig */
        $sortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);;

        $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $mockCriteria,
            $selectedFilters,
            $stubContext,
            $stubFacetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
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
