<?php

namespace Brera\Product\Block;

use Brera\Product\ProductSource;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Renderer\ThemeProductRenderingTestTrait;

/**
 * @covers \Brera\Product\Block\ProductDetailsPageBlock
 * @covers \Brera\Renderer\Block
 */
class ProductDetailsPageBlockTest extends \PHPUnit_Framework_TestCase
{
    use ThemeProductRenderingTestTrait;

    /**
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    protected function setUp()
    {
        $this->stubProduct = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createTemporaryThemeFiles();
    }

    protected function tearDown()
    {
        $this->removeTemporaryThemeFiles();
    }

    /**
     * @test
     */
    public function itShouldReturnBlockOutput()
    {
        $templateDirectoryPath = $this->getTemplateDirectoryPath();
        $productDetailsPageBlock = new ProductDetailsPageBlock(
            $templateDirectoryPath . '/1column.phtml',
            $this->stubProduct
        );
        $result = $productDetailsPageBlock->render();

        $this->assertEquals("- Hi, I'm a 1 column template!<br/>\n", $result);
    }

    /**
     * @test
     */
    public function itShouldAddChildBlockAndRenderItsContent()
    {
        $templateDirectoryPath = $this->getTemplateDirectoryPath();
        $childBlock = new ProductImageGallery($templateDirectoryPath . '/gallery.phtml', $this->stubProduct);
        $productDetailsPageBlock = new ProductDetailsPageBlock(
            $templateDirectoryPath . '/1column.phtml',
            $this->stubProduct
        );
        $productDetailsPageBlock->addChildBlock('foo', $childBlock);

        $result = $productDetailsPageBlock->getChildOutput('foo');

        $this->assertEquals("- And I'm a gallery template.\n", $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductAttributeValue()
    {
        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn('foo');

        $productDetailsPageBlock = new ProductDetailsPageBlock('bar.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->getProductAttributeValue('name');

        $this->assertEquals('foo', $result);
    }

    /**
     * @test
     */
    public function itShouldReturnEmptyStringIfAttributeIsNotFound()
    {
        $stubException = $this->getMock(ProductAttributeNotFoundException::class);

        $this->stubProduct->expects($this->once())
            ->method('getAttributeValue')
            ->willThrowException($stubException);

        $productDetailsPageBlock = new ProductDetailsPageBlock('foo.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->getProductAttributeValue('bar');

        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProductId()
    {
        $this->stubProduct->expects($this->once())
            ->method('getId')
            ->willReturn('foo');

        $productDetailsPageBlock = new ProductDetailsPageBlock('bar.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->getProductId();

        $this->assertEquals('foo', $result);
    }
}
