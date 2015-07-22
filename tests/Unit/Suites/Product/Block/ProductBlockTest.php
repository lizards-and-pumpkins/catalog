<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\Product;
use Brera\Product\ProductAttribute;
use Brera\Product\ProductAttributeList;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Renderer\Block;
use Brera\Renderer\BlockRenderer;

/**
 * @covers \Brera\Product\Block\ProductBlock
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\Image
 */
class ProductBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductBlock
     */
    private $productBlock;

    protected function setUp()
    {
        /** @var  $stubBlockRenderer BlockRenderer|\PHPUnit_Framework_MockObject_MockObject */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->productBlock = new ProductBlockTestStub($stubBlockRenderer, 'foo.phtml', 'foo', $this->stubProduct);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->productBlock);
    }

    public function testProductAttributeValueIsReturned()
    {
        $attributeCode = 'name';
        $attributeValue = 'foo';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with($attributeCode)
            ->willReturn($attributeValue);

        $this->assertEquals($attributeValue, $this->productBlock->getProductAttributeValue($attributeCode));
    }

    public function testEmptyStringIsReturnedIfAttributeIsNotFound()
    {
        $attributeCode = 'foo';
        $this->stubProduct->method('getAttributeValue')
            ->with($attributeCode)
            ->willThrowException(new ProductAttributeNotFoundException);

        $result = $this->productBlock->getProductAttributeValue($attributeCode);
        $this->assertSame('', $result);
    }

    public function testProductUrlIsReturned()
    {
        $urlKey = 'foo';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('url_key')
            ->willReturn($urlKey);

        $result = $this->productBlock->getProductUrl();

        $this->assertEquals($urlKey, $result);
    }

    public function testInstanceOfImageIsReturned()
    {
        $stubAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);

        $mockProductAttributeList = $this->getMock(ProductAttributeList::class);
        $mockProductAttributeList->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturn($stubAttribute);

        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('image')
            ->willReturn($mockProductAttributeList);

        $result = $this->productBlock->getMainProductImage();

        $this->assertInstanceOf(Image::class, $result);
    }
}
