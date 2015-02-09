<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
use Brera\Environment\Environment;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\SnippetResult;
use Brera\ThemeLocator;
use Brera\Renderer\ThemeProductRenderingTestTrait;

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
    use ThemeProductRenderingTestTrait;

    /**
     * @var ProductDetailViewSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var EnvironmentSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironmentSource;

    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironment;

    /**
     * @var ThemeLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubThemeLocator;

    public function setUp()
    {
        $this->createTemporaryThemeFiles();

        $stubKeyGenerator = $this->getMock(HardcodedProductDetailViewSnippetKeyGenerator::class, ['getKey']);
        $stubKeyGenerator->expects($this->any())
            ->method('getKey')
            ->willReturn('test');

        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->stubThemeLocator = $this->getMock(ThemeLocator::class);
        $this->stubThemeLocator->expects($this->any())
            ->method('getThemeDirectoryForEnvironment')
            ->willReturn($this->getThemeDirectoryPath());

        $this->snippetRenderer = new ProductDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $stubKeyGenerator,
            $this->stubThemeLocator
        );

        $this->stubEnvironment = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubEnvironmentSource->expects($this->any())->method('extractEnvironments')
            ->willReturn([$this->stubEnvironment]);
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
        $stubProductSource = $this->getStubProductSource();

        $result = $this->snippetRenderer->render($stubProductSource, $this->stubEnvironmentSource);
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldAddOneOrMoreSnippetsToTheSnippetList()
    {
        $stubProductSource = $this->getStubProductSource();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('add')
            ->with($this->isInstanceOf(SnippetResult::class));

        $this->snippetRenderer->render($stubProductSource, $this->stubEnvironmentSource);
    }

    /**
     * @test
     */
    public function itShouldRenderBlockContent()
    {
        $stubEnvironment = $this->getMock(Environment::class);

        $productIdString = 'test-123';
        $productNameString = 'Test Name';
        $stubProductSource = $this->getStubProductSource();
        $stubProductSource->getId()->expects($this->any())
            ->method('getId')->willReturn($productIdString);
        $stubProductSource->getId()->expects($this->any())
            ->method('__toString')->willReturn($productIdString);
        $stubProductSource->getProductForEnvironment($stubEnvironment)->expects($this->any())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn($productNameString);

        $transport = '';
        $this->mockSnippetResultList->expects($this->once())
            ->method('add')
            ->willReturnCallback(function ($snippetResult) use (&$transport) {
                $transport = $snippetResult;
            });

        $this->snippetRenderer->render($stubProductSource, $this->stubEnvironmentSource);

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
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductSource
     */
    private function getStubProductSource()
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

        $stubProductSource = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubProductSource->expects($this->any())
            ->method('getId')
            ->willReturn($stubProductId);

        $stubProductSource->expects($this->any())
            ->method('getProductForEnvironment')
            ->willReturn($stubProduct);

        return $stubProductSource;
    }
}
