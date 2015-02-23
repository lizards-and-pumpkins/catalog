<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\TestFileFixtureTrait;
use Brera\Renderer\Block;

/**
 * @covers \Brera\Product\Block\ProductDetailsPageBlock
 * @uses   \Brera\Renderer\Block
 */
class ProductDetailsPageBlockTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductInContextDetailViewSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRenderer;

    protected function setUp()
    {
        $this->stubRenderer = $this->getMock(ProductDetailViewBlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);
    }

    /**
     * @return ProductDetailsPageBlock
     */
    private function createInstance()
    {
        $template = 'dummy-template.phtml';
        $blockName = 'test-name';
        return new ProductDetailsPageBlock($this->stubRenderer, $template, $blockName, $this->stubProduct);
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

        $productDetailsPageBlock = $this->createInstance();
        $result = $productDetailsPageBlock->getProductAttributeValue($attributeCode);
        $this->assertSame('', $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductId()
    {
        $this->stubProduct->expects($this->once())
            ->method('getId')
            ->willReturn('foo');

        $productDetailsPageBlock = $this->createInstance();
        $result = $productDetailsPageBlock->getProductId();

        $this->assertEquals('foo', $result);
    }
}
