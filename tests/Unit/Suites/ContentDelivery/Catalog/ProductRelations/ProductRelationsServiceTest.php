<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelationsService
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
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductJsonSnippetKeyGenerator;

    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var ProductRelationTypeCode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductRelationTypeCode;

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
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockProductJsonSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubContext = $this->getMock(Context::class);
        
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductRelationTypeCode = $this->getMock(ProductRelationTypeCode::class, [], [], '', false);
        
        $this->productRelationsService = new ProductRelationsService(
            $this->stubProductRelationsLocator,
            $this->mockDataPoolReader,
            $this->mockProductJsonSnippetKeyGenerator,
            $this->stubContext
        );
    }
    
    public function testItDelegatesToTheProductRelationsInstanceToFetchTheRelatedProducts()
    {
        $this->mockProductRelations->expects($this->once())->method('getById')->willReturn([]);
        
        $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId
        );
    }

    public function testItFetchesTheRelatedProductJsonSnippetsFromTheDataPool()
    {
        $stubRelatedProductIds = [$this->getMock(ProductId::class, [], [], '', false)];
        $stubRelatedProductData = [
            ['Dummy Product Data 1'],
            ['Dummy Product Data 2'],
        ];
        $stubRelatedProductJsonSnippets = array_map('json_encode', $stubRelatedProductData);
        
        $this->mockProductRelations->method('getById')->willReturn($stubRelatedProductIds);

        $this->mockDataPoolReader->method('getSnippets')->willReturn($stubRelatedProductJsonSnippets);
        
        $this->assertSame($stubRelatedProductData, $this->productRelationsService->getRelatedProductData(
            $this->stubProductRelationTypeCode,
            $this->stubProductId
        ));
    }
}
