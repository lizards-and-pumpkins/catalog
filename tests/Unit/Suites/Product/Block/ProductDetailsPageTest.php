<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Renderer\Layout;

/**
 * @covers \Brera\Product\Block\ProductDetailsPage
 * @covers \Brera\Renderer\Block
 */
class ProductDetailsPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailsPage
     */
    private $productDetailsPageBlock;

    /**
     * @var Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLayout;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    protected function setUp()
    {
        $this->stubLayout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productDetailsPageBlock = new ProductDetailsPage($this->stubLayout, $this->stubProduct);
    }

    /**
     * @test
     */
    public function itShouldReturnBlockOutput()
    {
        $this->stubLayout->expects($this->once())
            ->method('getAttribute')
            ->with('template')
            ->willReturn('theme/template/1column.phtml');

        $this->stubProduct->expects($this->once())
            ->method('getId')
            ->willReturn('test-123');

        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn('Test Name');

        $result = $this->productDetailsPageBlock->render();

        $this->assertEquals("- Hi, I'm a 1 column template of Test Name (test-123) product!<br/>\n", $result);
    }

    /**
     * @test
     */
    public function itShouldAddChildBlockAndRenderItsContent()
    {
        $stubChildBlockLayout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubChildBlockLayout->expects($this->any())
            ->method('getAttribute')
            ->willReturnMap([['name', 'foo'], ['template', 'theme/template/gallery.phtml']]);

        $childBlock = new ProductImageGallery($stubChildBlockLayout, $this->stubProduct);
        $this->productDetailsPageBlock->addChildBlock($childBlock);

        $result = $this->productDetailsPageBlock->getChildBlock('foo');

        $this->assertEquals("- And I'm a gallery template.\n", $result);
    }
}
