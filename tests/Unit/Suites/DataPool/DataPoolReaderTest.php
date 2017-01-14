<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\InvalidKeyValueStoreKeyException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Import\Product\ProductId;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
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
    public function testSnippetIsReturned(string $keyValueStorageReturn, array $expectedContent)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $this->assertEquals($expectedContent, $this->dataPoolReader->getChildSnippetKeys('some_key'));
    }

    /**
     * @return array[]
     */
    public function snippetsProvider() : array
    {
        return [
            [json_encode(false), []],
            ['[]', []],
            ['{}', []],
            [json_encode(['test_key1', 'test_key2', 'some_key']), ['test_key1', 'test_key2', 'some_key']],
        ];
    }

    public function testExceptionIsThrownIfJsonIsBroken()
    {
        $this->expectException(\RuntimeException::class);
        $this->addGetMethodToStubKeyValueStore('not a JSON string');
        $this->dataPoolReader->getChildSnippetKeys('some_key');
    }

    public function testOnlyStringKeyIsAcceptedForSnippets()
    {
        $this->expectException(\TypeError::class);
        $this->dataPoolReader->getChildSnippetKeys(1);
    }

    public function testOnlyStringKeysAreAcceptedForGetSnippet()
    {
        $this->expectException(\TypeError::class);
        $this->dataPoolReader->getSnippet(1);
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
        $stubQueryOptions = $this->createMock(QueryOptions::class);

        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $stubCriteria */
        $stubCriteria = $this->createMock(SearchCriteria::class);

        $this->getMockSearchEngine()->expects($this->once())->method('query')->with($stubCriteria, $stubQueryOptions);

        $this->dataPoolReader->getSearchResults($stubCriteria, $stubQueryOptions);
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
        $stubCriteria = $this->createMock(SearchCriteria::class);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        /** @var SortBy|\PHPUnit_Framework_MockObject_MockObject $stubSortBy */
        $stubSortBy = $this->createMock(SortBy::class);

        $rowsPerPage = 1000;
        $pageNumber = 1;
        
        /** @var ProductId[]|\PHPUnit_Framework_MockObject_MockObject[] $matchingProductIds */
        $matchingProductIds = [$this->createMock(ProductId::class)];

        /** @var SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject $stubSearchResponse */
        $stubSearchResponse = $this->createMock(SearchEngineResponse::class);
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
        $stubCriteria = $this->createMock(SearchCriteria::class);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        /** @var SortBy|\PHPUnit_Framework_MockObject_MockObject $stubSortBy */
        $stubSortBy = $this->createMock(SortBy::class);

        $rowsPerPage = 1000;
        $pageNumber = 1;
        
        /** @var ProductId[]|\PHPUnit_Framework_MockObject_MockObject[] $matchingProductIds */
        $matchingProductIds = ['non-numeric-key' => $this->createMock(ProductId::class)];

        /** @var SearchEngineResponse|\PHPUnit_Framework_MockObject_MockObject $stubSearchResponse */
        $stubSearchResponse = $this->createMock(SearchEngineResponse::class);
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
