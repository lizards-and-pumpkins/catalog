<?php

namespace Unit\Suites\Product\Block;

use Brera\Image;
use Brera\Product\Block\ProductImageGallery;
use Brera\Product\ProductSource;
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
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSource;

    protected function setUp()
    {
        $this->stubProductSource = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function itShouldReturnMainProductImage()
    {
        $stubFileAttribute = $this->getMockBuilder(ProductAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubFileAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('foo.png');

        $stubLabelAttribute = $this->getMockBuilder(ProductAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubLabelAttribute->expects($this->once())
            ->method('getValue')
            ->willReturn('bar');

        $stubAttributeList = $this->getMockBuilder(ProductAttributeList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubAttributeList->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnMap([['file', $stubFileAttribute], ['label', $stubLabelAttribute]]);

        $this->stubProductSource->expects($this->once())
            ->method('getAttributeValue')
            ->with('image')
            ->willReturn($stubAttributeList);

        $block = new ProductImageGallery('foo.phtml', $this->stubProductSource);
        $result = $block->getMainProductImage();

        $this->assertInstanceOf(Image::class, $result);
        $this->assertEquals(Image::MEDIA_DIR . DIRECTORY_SEPARATOR . 'foo.png', $result->getSrc());
        $this->assertEquals('bar', $result->getLabel());
    }
}
