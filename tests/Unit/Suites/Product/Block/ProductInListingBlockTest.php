<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Product\ProductInListingBlockRenderer;
use Brera\TestFileFixtureTrait;
use Brera\Renderer\Block;

/**
 * @covers \Brera\Product\Block\ProductInListingBlock
 * @uses   \Brera\Renderer\Block
 */
class ProductInListingBlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductInListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRenderer;

    protected function setUp()
    {
        $this->stubRenderer = $this->getMock(ProductInListingBlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);
    }

    /**
     * @return ProductInListingBlock
     */
    private function createInstance()
    {
        $template = 'dummy-template.phtml';
        $blockName = 'test-name';
        return new ProductInListingBlock($this->stubRenderer, $template, $blockName, $this->stubProduct);
    }

    /**
     * @test
     */
    public function itShouldBeABlock()
    {
        $this->assertInstanceOf(Block::class, $this->createInstance());
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

        $block = $this->createInstance();

        $this->assertEquals($attributeValue, $block->getProductAttributeValue($attributeCode));
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyStringIfAttributeIsNotFound()
    {
        $stubException = $this->getMock(ProductAttributeNotFoundException::class);

        $attributeCode = 'bar';
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with($attributeCode)
            ->willThrowException($stubException);

        $productInListingBlock = $this->createInstance();
        $result = $productInListingBlock->getProductAttributeValue($attributeCode);
        $this->assertSame('', $result);
    }
}
