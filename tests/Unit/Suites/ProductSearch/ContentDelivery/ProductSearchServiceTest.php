<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductListing\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnsupportedSortOrderException;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 */
class ProductSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var ProductJsonService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonService;

    private $maxAllowedProductsPerPage = 10;

    /**
     * @var string[]
     */
    private $sortableAttributeCodes = ['foo', 'bar'];

    /**
     * @var ProductSearchService
     */
    private $service;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    private function createSortOrderConfigWithGivenAttributeCode(string $attributeCode) : SortOrderConfig
    {
        return SortOrderConfig::create(AttributeCode::fromString($attributeCode), SortOrderDirection::create('asc'));
    }

    final protected function setUp()
    {
        $this->stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubProductJsonService = $this->createMock(ProductJsonService::class);

        $this->service = new ProductSearchService(
            $this->stubDataPoolReader,
            $this->stubProductJsonService,
            $this->maxAllowedProductsPerPage,
            ...$this->sortableAttributeCodes
        );

        $this->stubContext = $this->createMock(Context::class);
    }

    public function testReturnsAnEmptyResultIfNoProductsMatchQueryString()
    {
        $queryString = 'foo';

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);

        $this->stubDataPoolReader->method('getProductIdsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $rowsPerPage = 10;
        $pageNumber = 0;
        $testSortOrderConfig = $this->createSortOrderConfigWithGivenAttributeCode('bar');

        $result = $this->service->getData(
            $queryString,
            $this->stubContext,
            $rowsPerPage,
            $pageNumber,
            $testSortOrderConfig
        );

        $this->assertSame(['total' => 0, 'data' => []], $result);
    }

    public function testReturnsSetOfMatchingProductsData()
    {
        $queryString = 'foo';

        $stubProductIds = [$this->createMock(ProductId::class), $this->createMock(ProductId::class)];
        $dummyProductDataArray = [['Dummy product A data'], ['Dummy product B data']];

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn($stubProductIds);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn(count($dummyProductDataArray));

        $this->stubDataPoolReader->method('getSearchResultsMatchingString')->willReturn($stubSearchEngineResponse);
        $this->stubProductJsonService->method('get')->willReturn($dummyProductDataArray);

        $rowsPerPage = 10;
        $pageNumber = 0;
        $testSortOrderConfig = $this->createSortOrderConfigWithGivenAttributeCode('bar');

        $result = $this->service->getData(
            $queryString,
            $this->stubContext,
            $rowsPerPage,
            $pageNumber,
            $testSortOrderConfig
        );

        $this->assertSame(['total' => count($dummyProductDataArray), 'data' => $dummyProductDataArray], $result);
    }

    public function testThrowsAnExceptionIfRequestedSortOrderIsNotAllowed()
    {
        $unsupportedSortAttributeCode = 'baz';

        $this->expectException(UnsupportedSortOrderException::class);
        $this->expectExceptionMessage(sprintf('Sorting by "%s" is not supported', $unsupportedSortAttributeCode));

        $queryString = 'foo';
        $rowsPerPage = 10;
        $pageNumber = 0;
        $testSortOrderConfig = $this->createSortOrderConfigWithGivenAttributeCode($unsupportedSortAttributeCode);

        $this->service->getData($queryString, $this->stubContext, $rowsPerPage, $pageNumber, $testSortOrderConfig);
    }

    public function testThrowsAnExceptionIfInvalidNumberOfProductsPerPageTypeIsPassed()
    {
        $this->expectException(\TypeError::class);

        $queryString = 'foo';
        $rowsPerPage = [];
        $pageNumber = 0;
        $testSortOrderConfig = $this->createSortOrderConfigWithGivenAttributeCode('bar');

        $this->service->getData($queryString, $this->stubContext, $rowsPerPage, $pageNumber, $testSortOrderConfig);
    }

    public function testThrowsAnExceptionIfRequestedNumberOfProductIsHigherThanAllowed()
    {
        $rowsPerPage = $this->maxAllowedProductsPerPage + 1;

        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        $this->expectExceptionMessage(sprintf(
            'Maximum allowed number of products per page is %d, got %d.',
            $this->maxAllowedProductsPerPage,
            $rowsPerPage
        ));

        $queryString = 'foo';
        $pageNumber = 0;
        $testSortOrderConfig = $this->createSortOrderConfigWithGivenAttributeCode('bar');

        $this->service->getData($queryString, $this->stubContext, $rowsPerPage, $pageNumber, $testSortOrderConfig);
    }
}
