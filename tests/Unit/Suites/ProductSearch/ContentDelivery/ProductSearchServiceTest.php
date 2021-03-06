<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult
 */
class ProductSearchServiceTest extends TestCase
{
    /**
     * @var DataPoolReader|MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var SearchCriteria|MockObject
     */
    private $stubGlobalProductListingCriteria;

    /**
     * @var ProductJsonService|MockObject
     */
    private $stubProductJsonService;

    /**
     * @var ProductSearchService
     */
    private $service;

    /**
     * @var SearchCriteria|MockObject
     */
    private $stubSearchCriteria;

    /**
     * @var QueryOptions|MockObject
     */
    private $stubQueryOptions;

    final protected function setUp(): void
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

    public function testReturnsAnEmptyResultIfNoProductsMatchQueryString(): void
    {
        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);

        $this->stubDataPoolReader->method('getSearchResults')->willReturn($stubSearchEngineResponse);

        $result = $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);

        $this->assertSame(0, $result->getTotalNumberOfResults());
    }

    public function testReturnsSetOfMatchingProductsData(): void
    {
        $stubProductIds = [$this->createMock(ProductId::class), $this->createMock(ProductId::class)];
        $dummyProductDataArray = [['Dummy product A data'], ['Dummy product B data']];

        /** @var FacetFieldCollection|MockObject $stubFacetFieldCollection */
        $stubFacetFieldCollection = $this->createMock(FacetFieldCollection::class);

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getProductIds')->willReturn($stubProductIds);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn(count($dummyProductDataArray));
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($stubFacetFieldCollection);

        $this->stubDataPoolReader->method('getSearchResults')->willReturn($stubSearchEngineResponse);
        $this->stubProductJsonService->method('get')->willReturn($dummyProductDataArray);

        $result = $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);
        $expectedResult = new ProductSearchResult(
            count($dummyProductDataArray),
            $dummyProductDataArray,
            $stubFacetFieldCollection
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testAppliesGlobalProductListingCriteriaToCriteriaSentToDataPool(): void
    {
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            $this->stubSearchCriteria,
            $this->stubGlobalProductListingCriteria
        );

        $this->stubDataPoolReader->expects($this->once())->method('getSearchResults')
            ->with($expectedCriteria);

        $this->service->query($this->stubSearchCriteria, $this->stubQueryOptions);
    }
}
