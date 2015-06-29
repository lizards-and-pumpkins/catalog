<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Product\ProductInListingBlockRenderer;
use Brera\TestFileFixtureTrait;
use Brera\Renderer\Block;

/**
 * @covers \Brera\Product\Block\ProductInListingBlock
 * @uses   \Brera\Product\Block\ProductBlock
 * @uses   \Brera\Renderer\Block
 */
class ProductInListingBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductInListingBlock
     */
    private $block;

    protected function setUp()
    {
        $stubRenderer = $this->getMock(ProductInListingBlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->block = new ProductInListingBlock($stubRenderer, 'foo.phtml', 'foo', $this->stubProduct);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->block);
    }
}
