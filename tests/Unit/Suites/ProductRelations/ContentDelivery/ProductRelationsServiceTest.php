<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductRelations\ProductRelations;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService
 */
class ProductRelationsServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRelations|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductRelations;

    /**
     * @var ProductRelationsLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductRelationsLocator;

    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var ProductRelationTypeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductRelationTypeCode;

    /**
     * @var ProductJsonService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonService;

    /**
     * @var ProductRelationsService
     */
    private $productRelationsService;

    /**
     * @var Context
     */
    private $stubContext;

    protected function setUp()
    {
        $this->mockProductRelations = $this->createMock(ProductRelations::class);
        $this->stubProductRelationsLocator = $this->createMock(ProductRelationsLocator::class);
        $this->stubProductRelationsLocator->method('locate')->willReturn($this->mockProductRelations);
        $this->stubProductJsonService = $this->createMock(ProductJsonService::class);
        $this->stubContext = $this->createMock(Context::class);
        
        $this->stubProductId = $this->createMock(ProductId::class);
        $this->stubProductRelationTypeCode = $this->createMock(ProductRelationTypeCode::class);
        
        $this->productRelationsService = new ProductRelationsService(
            $this->stubProductRelationsLocator,
            $this->stubProductJsonService
        );
    }
    
    public function testItDelegatesToTheProductRelationsInstanceToFetchTheRelatedProducts()
    {
        $this->mockProductRelations->expects($this->once())->method('getById')->willReturn([]);
        
        $result = $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId,
            $this->stubContext
        );
        $this->assertSame([], $result);
    }

    public function testItFetchesTheRelatedProductJsonSnippetsFromTheProductJsonService()
    {
        $stubRelatedProductIds = [$this->createMock(ProductId::class)];
        $stubRelatedProductData = [
            ['Dummy Product Data 1'],
            ['Dummy Product Data 2'],
        ];
        
        $this->mockProductRelations->method('getById')->willReturn($stubRelatedProductIds);

        $this->stubProductJsonService->method('get')->willReturn($stubRelatedProductData);
        
        $this->assertSame($stubRelatedProductData, $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId,
            $this->stubContext
        ));
    }
}
