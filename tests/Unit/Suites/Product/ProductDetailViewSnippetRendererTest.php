<?php

namespace Brera\Product;

use Brera\EnvironmentSource;
use Brera\Renderer\LayoutReader;
use Brera\Renderer\ThemeTestTrait;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\Environment;
use Brera\SnippetResult;

require_once __DIR__ . '/../Renderer/ThemeTestTrait.php';

/**
 * @covers \Brera\Product\ProductDetailViewSnippetRenderer
 * @covers \Brera\Renderer\BlockSnippetRenderer
 * @uses   \Brera\SnippetResult
 * @uses   \Brera\Product\HardcodedProductDetailViewSnippetKeyGenerator
 * @uses   \Brera\Product\Block\ProductDetailsPage
 * @uses   \Brera\Renderer\LayoutReader
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\XPathParser
 * @uses   \Brera\Renderer\Layout
 */
class ProductDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use ThemeTestTrait;

    /**
     * @var ProductDetailViewSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironmentSource;

    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironment;

    /**
     * @var LayoutReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLayoutReader;

    public function setUp()
    {
        $stubKeyGenerator = $this->getMock(HardcodedProductDetailViewSnippetKeyGenerator::class, ['getKey']);
        $stubKeyGenerator->expects($this->any())
            ->method('getKey')
            ->willReturn('test');

        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->stubLayoutReader = $this->getMock(LayoutReader::class);

        $this->snippetRenderer = new ProductDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $stubKeyGenerator,
            $this->stubLayoutReader
        );

        $this->stubEnvironment = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubEnvironmentSource->expects($this->any())->method('extractEnvironments')
            ->willReturn([$this->stubEnvironment]);

        $this->createTemporaryThemeFiles();
    }

    protected function tearDown()
    {
        $this->removeTemporaryThemeFiles();
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

        $this->snippetRenderer->render($invalidSourceObject, $this->stubEnvironmentSource);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetResultList()
    {
        $stubProduct = $this->getStubProduct();

        $result = $this->snippetRenderer->render($stubProduct, $this->stubEnvironmentSource);
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldAddOneOrMoreSnippetsToTheSnippetList()
    {
        $stubProduct = $this->getStubProduct();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('add')
            ->with($this->isInstanceOf(SnippetResult::class));

        $this->snippetRenderer->render($stubProduct, $this->stubEnvironmentSource);
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
        $this->mockSnippetResultList->expects($this->once())
            ->method('add')
            ->willReturnCallback(function ($snippetResult) use (&$transport) {
                $transport = $snippetResult;
            });

        $this->snippetRenderer->render($stubProduct, $this->stubEnvironmentSource);

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
}
