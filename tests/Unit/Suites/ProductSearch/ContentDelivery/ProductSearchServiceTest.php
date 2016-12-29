<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
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

    /**
     * @var ProductSearchService
     */
    private $service;

    final protected function setUp()
    {
        $this->stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubProductJsonService = $this->createMock(ProductJsonService::class);

        $this->service = new ProductSearchService($this->stubDataPoolReader, $this->stubProductJsonService);
    }

    public function testReturnsAnEmptyResultIfNoProductsMatchQueryString()
    {
        $queryString = 'foo';
        $stubQueryOptions = $this->createMock(QueryOptions::class);

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);

        $this->stubDataPoolReader->method('getProductIdsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $result = $this->service->query($queryString, $stubQueryOptions);

        $this->assertSame(['total' => 0, 'data' => []], $result);
    }

    public function testReturnsSetOfMatchingProductsData()
    {
        $queryString = 'foo';
        $stubQueryOptions = $this->createMock(QueryOptions::class);

        $stubProductIds = [$this->createMock(ProductId::class), $this->createMock(ProductId::class)];
        $dummyProductDataArray = [['Dummy product A data'], ['Dummy product B data']];

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn($stubProductIds);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn(count($dummyProductDataArray));

        $this->stubDataPoolReader->method('getSearchResultsMatchingString')->willReturn($stubSearchEngineResponse);
        $this->stubProductJsonService->method('get')->willReturn($dummyProductDataArray);

        $result = $this->service->query($queryString, $stubQueryOptions);

        $this->assertSame(['total' => count($dummyProductDataArray), 'data' => $dummyProductDataArray], $result);
    }
}
