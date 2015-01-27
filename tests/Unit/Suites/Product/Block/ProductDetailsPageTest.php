<?php

namespace Brera\Product\Block;

use Brera\Product\Product;

/**
 * @covers \Brera\Product\Block\ProductDetailsPage
 * @covers \Brera\Renderer\Block
 */
class ProductDetailsPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    protected function setUp()
    {
        $this->stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function itShouldReturnBlockOutput()
    {
        $productDetailsPageBlock = new ProductDetailsPage('theme/template/1column.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->render();

        $this->assertEquals("- Hi, I'm a 1 column template!<br/>\n", $result);
    }

    /**
     * @test
     */
    public function itShouldAddChildBlockAndRenderItsContent()
    {
        $childBlock = new ProductImageGallery('theme/template/gallery.phtml', $this->stubProduct);
        $productDetailsPageBlock = new ProductDetailsPage('theme/template/1column.phtml', $this->stubProduct);
        $productDetailsPageBlock->addChildBlock('foo', $childBlock);

        $result = $productDetailsPageBlock->getChildOutput('foo');

        $this->assertEquals("- And I'm a gallery template.\n", $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductInstance()
    {
        $productDetailsPageBlock = new ProductDetailsPage('foo.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->getProduct();

        $this->assertInstanceOf(Product::class, $result);
    }
}
