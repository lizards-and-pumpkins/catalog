<?php

namespace Brera\Product\Block;

use Brera\Product\ProductSource;
use Brera\Renderer\ThemeTestTrait;

require_once __DIR__ . '/../../Renderer/ThemeTestTrait.php';

/**
 * @covers \Brera\Product\Block\ProductDetailsPage
 * @covers \Brera\Renderer\Block
 */
class ProductDetailsPageTest extends \PHPUnit_Framework_TestCase
{
    use ThemeTestTrait;

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
        $productDetailsPageBlock = new ProductDetailsPage(
            $templateDirectoryPath . '/1column.phtml', $this->stubProduct
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
        $productDetailsPageBlock = new ProductDetailsPage(
            $templateDirectoryPath . '/1column.phtml', $this->stubProduct
        );
        $productDetailsPageBlock->addChildBlock('foo', $childBlock);

        $result = $productDetailsPageBlock->getChildOutput('foo');

        $this->assertEquals("- And I'm a gallery template.\n", $result);
    }

    /**
     * @test
     */
    public function itShouldReturnProduct()
    {
        $productDetailsPageBlock = new ProductDetailsPage('foo.phtml', $this->stubProduct);
        $result = $productDetailsPageBlock->getProduct();

        $this->assertSame($this->stubProduct, $result);
    }
}
