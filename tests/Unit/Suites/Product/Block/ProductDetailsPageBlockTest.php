<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Renderer\Block;

/**
 * @covers \Brera\Product\Block\ProductDetailsPageBlock
 * @uses   \Brera\Product\Block\ProductBlock
 * @uses   \Brera\Renderer\Block
 */
class ProductDetailsPageBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductDetailsPageBlock
     */
    private $block;

    protected function setUp()
    {
        $stubRenderer = $this->getMock(ProductDetailViewBlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->block = new ProductDetailsPageBlock($stubRenderer, 'foo.phtml', 'foo', $this->stubProduct);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->block);
    }

    public function testProductIdIsReturned()
    {
        $this->stubProduct->expects($this->once())
            ->method('getId')
            ->willReturn('foo');

        $result = $this->block->getProductId();

        $this->assertEquals('foo', $result);
    }
}
