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
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getValue')->willReturn($testAttributeValue);

        $this->stubProductAttributeList->method('getAttribute')
            ->with($testAttributeCode)
            ->willReturn($stubProductAttribute);

        $this->assertSame($testAttributeValue, $this->product->getAttributeValue($testAttributeCode));
    }

    public function testEmptyStringIsReturnedIfAttributeIsNotFound()
    {
        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getValue')->willThrowException(new ProductAttributeNotFoundException);

        $this->stubProductAttributeList->method('getAttribute')->willReturn($stubProductAttribute);

        $result = $this->product->getAttributeValue('whatever');
        $this->assertSame('', $result);
    }
}
