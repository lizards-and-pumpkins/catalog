<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\ProductDetailViewBlockRenderer;
use Brera\Product\Product;
use Brera\Product\ProductAttribute;
use Brera\Product\ProductAttributeList;

/**
 * @covers \Brera\Product\Block\ProductImageGallery
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\Image
 */
class ProductImageGalleryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductDetailViewBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRenderer;

    protected function setUp()
    {
        $this->stubRenderer = $this->getMock(ProductDetailViewBlockRenderer::class, [], [], '', false);
        $this->stubProduct = $this->getMock(Product::class, [], [], '', false);
    }

    /**
     * @return ProductImageGallery
     */
    private function createInstance()
    {
        $template = 'dummy-template.phtml';
        $blockName = 'test-name';
        return new ProductImageGallery($this->stubRenderer, $template, $blockName, $this->stubProduct);
    }

    /**
     * @test
     */
    public function itShouldReturnMainProductImage()
    {
        $stubFileAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubFileAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('foo.png');

        $stubLabelAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubLabelAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('bar');

        $stubAttributeList = $this->getMock(ProductAttributeList::class, [], [], '', false);
        $stubAttributeList->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->willReturnMap([['file', $stubFileAttribute], ['label', $stubLabelAttribute]]);

        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('image')
            ->willReturn($stubAttributeList);

        $block = $this->createInstance();
        $result = $block->getMainProductImage();

        $this->assertInstanceOf(Image::class, $result);
        $this->assertEquals(Image::MEDIA_DIR . DIRECTORY_SEPARATOR . 'foo.png', $result->getSrc());
        $this->assertEquals('bar', $result->getLabel());
    }
}
