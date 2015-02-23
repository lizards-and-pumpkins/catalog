<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\Context\Context;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\Renderer\ThemeProductRenderingTestTrait;

/**
 * @covers \Brera\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \Brera\SnippetResult
 * @uses   \Brera\Product\Block\ProductDetailsPageBlock
 * @uses   \Brera\Renderer\LayoutReader
 * @uses   \Brera\Renderer\Block
 * @uses   \Brera\XPathParser
 * @uses   \Brera\Renderer\Layout
 */
class ProductSourceDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use ThemeProductRenderingTestTrait;

    /**
     * @var ProductSourceDetailViewSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var ProductInContextDetailViewSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductInContextDetailViewRenderer;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $rendererClass = ProductInContextDetailViewSnippetRenderer::class;
        $this->mockProductInContextDetailViewRenderer = $this->getMock($rendererClass, [], [], '', false);
        $this->mockProductInContextDetailViewRenderer->expects($this->any())
            ->method('render')
            ->willReturn($this->mockSnippetResultList);


        $this->snippetRenderer = new ProductSourceDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->mockProductInContextDetailViewRenderer
        );

        $this->stubContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubContextSource->expects($this->any())->method('extractContexts')
            ->willReturn([$this->stubContext]);
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
        /** @var ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject $invalidSourceObject */
        $invalidSourceObject = $this->getMockBuilder(ProjectionSourceData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->snippetRenderer->render($invalidSourceObject, $this->stubContextSource);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetResultList()
    {
        $stubProductSource = $this->getStubProductSource();

        $result = $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldMergeMoreSnippetsToTheSnippetList()
    {
        $stubProductSource = $this->getStubProductSource();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('merge')
            ->with($this->isInstanceOf(SnippetResultList::class));

        $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductSource
     */
    private function getStubProductSource()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())
            ->method('getId')
            ->willReturn($stubProductId);

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->expects($this->any())
            ->method('getId')
            ->willReturn($stubProductId);
        $stubProductSource->expects($this->any())
            ->method('getProductForContext')
            ->willReturn($stubProduct);

        return $stubProductSource;
    }
}
