<?php

namespace Brera\Product;

use Brera\Renderer\LayoutReader;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\Environment;
use Brera\SnippetResult;

/**
 * @covers \Brera\Product\ProductDetailViewSnippetRenderer
 * @covers \Brera\Renderer\BlockSnippetRenderer
 * @uses \Brera\SnippetResult
 * @uses \Brera\Product\HardcodedProductDetailViewSnippetKeyGenerator
 * @uses \Brera\Product\Block\ProductDetailsPage
 * @uses \Brera\Renderer\LayoutReader
 * @uses \Brera\Renderer\Block
 * @uses \Brera\XPathParser
 * @uses \Brera\Renderer\Layout
 */
class ProductDetailViewSnippetRendererTest	extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ProductDetailViewSnippetRenderer
	 */
	private $snippetRenderer;

	/**
	 * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubSnippetResultList;

	/**
	 * @var Environment|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubEnvironment;

	/**
	 * @var LayoutReader|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubLayoutReader;

	protected function setUp()
	{
		$stubKeyGenerator = $this->getMock(HardcodedProductDetailViewSnippetKeyGenerator::class, ['getKey']);
		$stubKeyGenerator->expects($this->any())
			->method('getKey')
			->willReturn('test');

		$this->stubSnippetResultList = $this->getMock(SnippetResultList::class);

		$this->stubLayoutReader = $this->getMock(LayoutReader::class);

		$this->snippetRenderer = new ProductDetailViewSnippetRenderer(
			$this->stubSnippetResultList,
			$stubKeyGenerator,
			$this->stubLayoutReader
		);

		$this->stubEnvironment = $this->getMockBuilder(Environment::class)
			->disableOriginalConstructor()
			->getMock();

		$this->createTemporaryLayoutFile();
	}

	protected function tearDown()
	{
		$this->removeTemporaryLayoutFile();
	}

	/**
	 * @test
	 */
	public function itShouldImplementSnippetRenderer()
	{
		$this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
	}

	/**
	 * @test
	 * @expectedException \Brera\Product\InvalidArgumentException
	 */
	public function itShouldOnlyAcceptProductsForRendering()
	{
		$invalidSourceObject = $this->getMockBuilder(ProjectionSourceData::class)
			->disableOriginalConstructor()
			->getMock();

		$this->snippetRenderer->render($invalidSourceObject, $this->stubEnvironment);
	}

	/**
	 * @test
	 */
	public function itShouldReturnASnippetResultList()
	{
		$stubProduct = $this->getStubProduct();

		$this->stubEnvironment->expects($this->once())
			->method('getThemeDirectory')
			->willReturn(sys_get_temp_dir());

		$result = $this->snippetRenderer->render($stubProduct, $this->stubEnvironment);
		$this->assertSame($this->stubSnippetResultList, $result);
	}

	/**
	 * @test
	 */
	public function itShouldAddOneOrMoreSnippetToTheSnippetList()
	{
		$stubProduct = $this->getStubProduct();

		$this->stubSnippetResultList->expects($this->atLeastOnce())
			->method('add')
			->with($this->isInstanceOf(SnippetResult::class));

		$this->stubEnvironment->expects($this->once())
			->method('getThemeDirectory')
			->willReturn(sys_get_temp_dir());

		$this->snippetRenderer->render($stubProduct, $this->stubEnvironment);
	}

	/**
	 * @test
	 */
	public function itShouldRenderBlockContent()
	{
		$productIdString = 'test-123';
		$productNameString = 'Test Name';
		$stubProduct = $this->getStubProduct();
		$stubProduct->getId()->expects($this->any())
			->method('getId')->willReturn($productIdString);
		$stubProduct->getId()->expects($this->any())
			->method('__toString')->willReturn($productIdString);
		$stubProduct->expects($this->any())
			->method('getAttributeValue')
			->with('name')
			->willReturn($productNameString);

		$transport = '';
		$this->stubSnippetResultList->expects($this->once())
			->method('add')
			->willReturnCallback(function ($snippetResult) use (&$transport) {
				$transport = $snippetResult;
			});

		$this->stubEnvironment->expects($this->once())
			->method('getThemeDirectory')
			->willReturn(sys_get_temp_dir());

		$this->snippetRenderer->render($stubProduct, $this->stubEnvironment);

		/** @var $transport SnippetResult */
		$expected = <<<EOT
- Hi, I'm a 1 column template!<br/>
Product details page content

Test Name (test-123)

- And I'm a gallery template.

EOT;
		$this->assertEquals($expected, $transport->getContent());
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|Product
	 */
	private function getStubProduct()
	{
		$stubProductId = $this->getMockBuilder(ProductId::class)
			->disableOriginalConstructor()
			->getMock();

		$stubProduct = $this->getMockBuilder(Product::class)
			->disableOriginalConstructor()
			->getMock();

		$stubProduct->expects($this->any())
			->method('getId')
			->willReturn($stubProductId);

		return $stubProduct;
	}

	/**
	 * @return null
	 */
	private function createTemporaryLayoutFile()
	{
		$layoutDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'layout';

		if (!file_exists($layoutDirectoryPath) || !is_dir($layoutDirectoryPath)) {
			mkdir($layoutDirectoryPath);
		}

		$layoutFilePath = $layoutDirectoryPath . DIRECTORY_SEPARATOR . 'product_details_snippet.xml';

		if (file_exists($layoutFilePath)) {
			unlink($layoutFilePath);
		}

		$fileContent = <<<EOX
<?xml version="1.0"?>
<snippet>
    <block name="product_details_snippet" class="Brera\Renderer\Block" template="theme/template/1column.phtml">
        <block name="content" class="Brera\Product\Block\ProductDetailsPage" template="theme/template/view.phtml">
            <block name="image_gallery" class="Brera\Product\Block\ProductImageGallery" template="theme/template/gallery.phtml" />
        </block>
    </block>
</snippet>
EOX;

		file_put_contents($layoutFilePath, $fileContent);
	}

	/**
	 * @return null
	 */
	private function removeTemporaryLayoutFile()
	{
		$layoutDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'layout';

		if (!file_exists($layoutDirectoryPath) || !is_dir($layoutDirectoryPath)) {
			return null;
		}

		$layoutFilePath = $layoutDirectoryPath . DIRECTORY_SEPARATOR . 'product_details_snippet.xml';

		if (file_exists($layoutFilePath)) {
			unlink($layoutFilePath);
		}

		rmdir($layoutDirectoryPath);
	}
}
