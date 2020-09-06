<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeException;
use LizardsAndPumpkins\ProductRelations\Exception\UnknownProductRelationTypeException;
use LizardsAndPumpkins\ProductRelations\ProductRelations;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 */
class ProductRelationsLocatorTest extends TestCase
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
     * @var ProductRelations
     */
    private $stubProductRelationType;

    public function createTestProductRelation(): ProductRelations
    {
        return $this->stubProductRelationType;
    }
    
    final protected function setUp(): void
    {
        $this->testRelationTypeCode = ProductRelationTypeCode::fromString('test');
        $this->productRelationLocator = new ProductRelationsLocator();
        $this->stubProductRelationType = $this->createMock(ProductRelations::class);

        $this->productRelationLocator->register($this->testRelationTypeCode, [$this, 'createTestProductRelation']);
    }
    
    public function testItThrowsAnExceptionIfThereIsNoRelationForTheGivenTypeCode(): void
    {
        $this->expectException(UnknownProductRelationTypeException::class);
        $this->expectExceptionMessage('The product relation "unknown" is unknown');
        $this->productRelationLocator->locate(ProductRelationTypeCode::fromString('unknown'));
    }

    public function testItReturnsARegisteredProductRelation(): void
    {
        $result = $this->productRelationLocator->locate($this->testRelationTypeCode);
        $this->assertSame($this->stubProductRelationType, $result);
    }

    public function testItThrowsAnExceptionIfTheFactoryMethodReturnTypeIsInvalid(): void
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
