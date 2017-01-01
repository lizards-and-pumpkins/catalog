<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 */
class ProductSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubGlobalProductListingCriteria;

    /**
     * @var ProductJsonService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonService;

    /**
     * @var ProductSearchService
     */
    private $service;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchCriteria;

    /**
     * @var QueryOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueryOptions;

    final protected function setUp()
    {
        $this->stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubGlobalProductListingCriteria = $this->createMock(SearchCriteria::class);
        $this->stubProductJsonService = $this->createMock(ProductJsonService::class);

        $this->service = new ProductSearchService(
            $this->stubDataPoolReader,
            $this->stubGlobalProductListingCriteria,
            $this->stubProductJsonService
        );

        $this->stubSearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubQueryOptions = $this->createMock(QueryOptions::class);
    }

    public function testReturnsAnEmptyResultIfNoProductsMatchQueryString()
    {
        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);

        $this->stubDataPoolReader->method('getProductIdsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $result = $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);

        $this->assertSame(['total' => 0, 'data' => []], $result);
    }

    public function testReturnsSetOfMatchingProductsData()
    {
        $stubProductIds = [$this->createMock(ProductId::class), $this->createMock(ProductId::class)];
        $dummyProductDataArray = [['Dummy product A data'], ['Dummy product B data']];

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn($stubProductIds);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn(count($dummyProductDataArray));

        $this->stubDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
        $this->stubProductJsonService->method('get')->willReturn($dummyProductDataArray);

        $result = $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);

        $this->assertSame(['total' => count($dummyProductDataArray), 'data' => $dummyProductDataArray], $result);
    }

    public function testAppliesGlobalProductListingCriteriaToCriteriaSentToDataPool()
    {
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            $this->stubSearchCriteria,
            $this->stubGlobalProductListingCriteria
        );

        $this->stubDataPoolReader->expects($this->once())->method('getSearchResultsMatchingCriteria')
            ->with($expectedCriteria);

        $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);
    }
}
