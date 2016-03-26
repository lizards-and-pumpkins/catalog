<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode;
use LizardsAndPumpkins\ProductRecommendations\ProductRelations;

/**
 * @covers \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsService
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
        $this->mockProductRelations = $this->getMock(ProductRelations::class);
        $this->stubProductRelationsLocator = $this->getMock(ProductRelationsLocator::class);
        $this->stubProductRelationsLocator->method('locate')->willReturn($this->mockProductRelations);
        $this->stubProductJsonService = $this->getMock(ProductJsonService::class, [], [], '', false);
        $this->stubContext = $this->getMock(Context::class);
        
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductRelationTypeCode = $this->getMock(ProductRelationTypeCode::class, [], [], '', false);
        
        $this->productRelationsService = new ProductRelationsService(
            $this->stubProductRelationsLocator,
            $this->stubProductJsonService,
            $this->stubContext
        );
    }
    
    public function testItDelegatesToTheProductRelationsInstanceToFetchTheRelatedProducts()
    {
        $this->mockProductRelations->expects($this->once())->method('getById')->willReturn([]);
        
        $result = $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId
        );
        $this->assertSame([], $result);
    }

    public function testItFetchesTheRelatedProductJsonSnippetsFromTheProductJsonService()
    {
        $stubRelatedProductIds = [$this->getMock(ProductId::class, [], [], '', false)];
        $stubRelatedProductData = [
            ['Dummy Product Data 1'],
            ['Dummy Product Data 2'],
        ];
        
        $this->mockProductRelations->method('getById')->willReturn($stubRelatedProductIds);

        $this->stubProductJsonService->method('get')->willReturn($stubRelatedProductData);
        
        $this->assertSame($stubRelatedProductData, $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId
        ));
    }
}
