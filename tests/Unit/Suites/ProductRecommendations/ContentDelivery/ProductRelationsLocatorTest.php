<?php

namespace LizardsAndPumpkins\ProductRecommendations\ContentDelivery;

use LizardsAndPumpkins\ProductRecommendations\Exception\InvalidProductRelationTypeException;
use LizardsAndPumpkins\ProductRecommendations\Exception\UnknownProductRelationTypeException;
use LizardsAndPumpkins\ProductRecommendations\ProductRelations;

/**
 * @covers \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRecommendations\ContentDelivery\ProductRelationTypeCode
 */
class ProductRelationsLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRelationsLocator
     */
    private $productRelationLocator;

    /**
     * @var ProductRelationTypeCode
     */
    private $testRelationTypeCode;

    /**
     * @var ProductRelations|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductRelationType;

    /**
     * @return ProductRelations|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createTestProductRelation()
    {
        return $this->stubProductRelationType;
    }
    
    protected function setUp()
    {
        $this->testRelationTypeCode = ProductRelationTypeCode::fromString('test');
        $this->productRelationLocator = new ProductRelationsLocator();
        $this->stubProductRelationType = $this->createMock(ProductRelations::class);

        $this->productRelationLocator->register($this->testRelationTypeCode, [$this, 'createTestProductRelation']);
    }
    
    public function testItThrowsAnExceptionIfThereIsNoRelationForTheGivenTypeCode()
    {
        $this->expectException(UnknownProductRelationTypeException::class);
        $this->expectExceptionMessage('The product relation "unknown" is unknown');
        $this->productRelationLocator->locate(ProductRelationTypeCode::fromString('unknown'));
    }

    public function testItReturnsARegisteredProductRelation()
    {
        $result = $this->productRelationLocator->locate($this->testRelationTypeCode);
        $this->assertSame($this->stubProductRelationType, $result);
    }

    public function testItThrowsAnExceptionIfTheFactoryMethodReturnTypeIsInvalid()
    {
        $typeCode = ProductRelationTypeCode::fromString('invalid');
        $invalidFactoryMethod = function () {
            return new \stdClass();
        };
        $this->productRelationLocator->register($typeCode, $invalidFactoryMethod);
        
        $this->expectException(InvalidProductRelationTypeException::class);
        $this->expectExceptionMessage(
            'Product Relation Type "stdClass" has to implement the ProductRelationType interface'
        );
        $this->productRelationLocator->locate($typeCode);
    }
}
