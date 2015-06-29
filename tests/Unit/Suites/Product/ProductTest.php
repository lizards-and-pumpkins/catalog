<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;

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

    public function testProjectionSourceDataInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProjectionSourceData::class, $this->product);
    }

    public function testProductIdIsReturned()
    {
        $result = $this->product->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testAttributeValueIsReturned()
    {
        $testAttributeValue = 'test-name';
        $testAttributeCode = 'name';

        $stubProductAttribute = $this->getMockBuilder(ProductAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProductAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn($testAttributeValue);

        $this->stubProductAttributeList->expects($this->once())
            ->method('getAttribute')
            ->with($testAttributeCode)
            ->willReturn($stubProductAttribute);

        $this->assertSame($testAttributeValue, $this->product->getAttributeValue($testAttributeCode));
    }
}
