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
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->productBlock = new ProductBlockTestStub($stubBlockRenderer, 'foo.phtml', 'foo', $this->stubProduct);
    }

    /**
     * @test
     */
    public function itShouldExtendBlockClass()
    {
        $this->assertInstanceOf(Block::class, $this->productBlock);
    }

    /**
     * @test
     */
    public function itShouldReturnProductAttributeValue()
    {
        $attributeCode = 'name';
        $attributeValue = 'foo';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with($attributeCode)
            ->willReturn($attributeValue);

        $this->assertEquals($attributeValue, $this->productBlock->getProductAttributeValue($attributeCode));
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyStringIfAttributeIsNotFound()
    {
        $stubException = $this->getMock(ProductAttributeNotFoundException::class);

        $attributeCode = 'foo';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with($attributeCode)
            ->willThrowException($stubException);

        $result = $this->productBlock->getProductAttributeValue($attributeCode);
        $this->assertSame('', $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductUrl()
    {
        $urlKey = 'foo';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('url_key')
            ->willReturn($urlKey);

        $result = $this->productBlock->getProductUrl();

        $this->assertEquals($urlKey, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnImage()
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
