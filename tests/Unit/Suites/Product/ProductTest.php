<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductAttributeList;

    public function setUp()
    {
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductAttributeList = $this->getMock(ProductAttributeList::class);
        $this->product = new Product($this->stubProductId, $this->stubProductAttributeList);
    }

    public function testProductIdIsReturned()
    {
        $result = $this->product->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testAttributeValueIsReturned()
    {
        $dummyAttributeCode = 'foo';
        $dummyAttributeValue = 'bar';

        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getValue')->willReturn($dummyAttributeValue);

        $this->stubProductAttributeList->method('getAttributesWithCode')
            ->with($dummyAttributeCode)
            ->willReturn([$stubProductAttribute]);

        $this->assertSame($dummyAttributeValue, $this->product->getFirstValueOfAttribute($dummyAttributeCode));
    }

    public function testAllValuesOfProductAttributeAreReturned()
    {
        $dummyAttributeCode = 'foo';

        $dummyAttributeAValue = 'bar';
        $stubProductAttributeA = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttributeA->method('getValue')->willReturn($dummyAttributeAValue);

        $dummyAttributeBValue = 'baz';
        $stubProductAttributeB = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttributeB->method('getValue')->willReturn($dummyAttributeBValue);

        $this->stubProductAttributeList->method('getAttributesWithCode')
            ->with($dummyAttributeCode)
            ->willReturn([$stubProductAttributeA, $stubProductAttributeB]);

        $expectedValues = [$dummyAttributeAValue, $dummyAttributeBValue];
        $result = $this->product->getAllValuesOfAttribute($dummyAttributeCode);

        $this->assertSame($expectedValues, $result);
    }

    public function testArrayContainingOneEmptyStringIsReturnedIfAttributeIsNotFound()
    {
        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getValue')->willThrowException(new ProductAttributeNotFoundException);

        $this->stubProductAttributeList->method('getAttributesWithCode')->willReturn([$stubProductAttribute]);

        $result = $this->product->getAllValuesOfAttribute('whatever');

        $this->assertSame([''], $result);
    }

    public function testEmptyStringIsReturnedIfAttributeIsNotFound()
    {
        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getValue')->willThrowException(new ProductAttributeNotFoundException);

        $this->stubProductAttributeList->method('getAttributesWithCode')->willReturn([$stubProductAttribute]);

        $result = $this->product->getFirstValueOfAttribute('whatever');

        $this->assertSame('', $result);
    }
}
