<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;
    
    /**
     * @var Product
     */
    private $product;

	/**
	 * @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubProductAttributeList;
    
    public function setUp()
    {
        $this->stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();
	    $this->stubProductAttributeList = $this->getMock(ProductAttributeList::class);

	    $this->product = new Product($this->stubProductId, $this->stubProductAttributeList);
    }

    /**
     * @test
     */
    public function itShouldReturnTheProductId()
    {
        $result = $this->product->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheName()
    {
	    $testName = 'test-name';

	    $stubProductAttribute = $this->getMockBuilder(ProductAttribute::class)
	        ->disableOriginalConstructor()
	        ->getMock();
	    $stubProductAttribute->expects($this->once())
		    ->method('getValue')
		    ->willReturn($testName);

	    $this->stubProductAttributeList->expects($this->once())
		    ->method('getAttribute')
		    ->with('name')
		    ->willReturn($stubProductAttribute);

        $this->assertSame($testName, $this->product->getAttributeValue('name'));
    }
}
