<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductRelations\ProductRelations;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService
 */
class ProductRelationsServiceTest extends TestCase
{
    /**
     * @var ProductRelations|MockObject
     */
    private $mockProductRelations;

    /**
     * @var ProductRelationsLocator|MockObject
     */
    private $stubProductRelationsLocator;

    /**
     * @var ProductId|MockObject
     */
    private $stubProductId;

    /**
     * @var ProductRelationTypeCode|MockObject
     */
    private $stubProductRelationTypeCode;

    /**
     * @var ProductJsonService|MockObject
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

    final protected function setUp(): void
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
    
    public function testItDelegatesToTheProductRelationsInstanceToFetchTheRelatedProducts(): void
    {
        $this->mockProductRelations->expects($this->once())->method('getById')->willReturn([]);
        
        $result = $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId,
            $this->stubContext
        );
        $this->assertSame([], $result);
    }

    public function testItFetchesTheRelatedProductJsonSnippetsFromTheProductJsonService(): void
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
