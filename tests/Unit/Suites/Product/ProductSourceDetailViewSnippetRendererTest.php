<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\SampleContextSource;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSourceDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\Product\Block\ProductBlock
 * @uses   \LizardsAndPumpkins\Renderer\LayoutReader
 * @uses   \LizardsAndPumpkins\Renderer\Block
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\Renderer\Layout
 */
class ProductSourceDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSourceDetailViewSnippetRenderer
     */
    private $productSourceSnippetRenderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var ProductDetailViewInContextSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductDetailViewInContextRenderer;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $rendererClass = ProductDetailViewInContextSnippetRenderer::class;
        $this->mockProductDetailViewInContextRenderer = $this->getMock($rendererClass, [], [], '', false);
        $this->mockProductDetailViewInContextRenderer->method('render')
            ->willReturn($this->mockSnippetList);
        $this->mockProductDetailViewInContextRenderer->method('getUsedContextParts')
            ->willReturn(['version']);
        

        $this->productSourceSnippetRenderer = new ProductSourceDetailViewSnippetRenderer(
            $this->mockSnippetList,
            $this->mockProductDetailViewInContextRenderer
        );

        $this->stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getContextsForParts')
            ->willReturn([$this->stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->productSourceSnippetRenderer);
    }

    public function testOnlyProductsAreAcceptedForRendering()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->productSourceSnippetRenderer->render('invalid-projection-source-data', $this->stubContextSource);
    }

    public function testSnippetListIsReturned()
    {
        $stubProductSource = $this->getStubProductSource();

        $result = $this->productSourceSnippetRenderer->render($stubProductSource, $this->stubContextSource);
        $this->assertSame($this->mockSnippetList, $result);
    }

    public function testSnippetsAreMergedIntoSnippetList()
    {
        $stubProductSource = $this->getStubProductSource();

        $this->mockSnippetList->expects($this->atLeastOnce())
            ->method('merge')
            ->with($this->isInstanceOf(SnippetList::class));

        $this->productSourceSnippetRenderer->render($stubProductSource, $this->stubContextSource);
    }

    public function testUsedContextPartsAreRequestedFromSnippetRendererAndPassedToContextBuilder()
    {
        $contextParts = ['version', 'website', 'locale'];

        $mockProductDetailViewInContextRenderer =
            $this->getMock(ProductDetailViewInContextSnippetRenderer::class, [], [], '', false);
        $mockProductDetailViewInContextRenderer->expects($this->once())
            ->method('getUsedContextParts')
            ->willReturn($contextParts);
        $mockProductDetailViewInContextRenderer->expects($this->atLeastOnce())
            ->method('render')
            ->willReturn($this->mockSnippetList);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $mockContextSource */
        $mockContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextsForParts'])
            ->getMockForAbstractClass();
        $mockContextSource->expects($this->once())
            ->method('getContextsForParts')
            ->with($contextParts)
            ->willReturn([$this->getMock(Context::class)]);
        
        $productSourceSnippetRenderer = new ProductSourceDetailViewSnippetRenderer(
            $this->mockSnippetList,
            $mockProductDetailViewInContextRenderer
        );

        $productSourceSnippetRenderer->render($this->getStubProductSource(), $mockContextSource);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductSource
     */
    private function getStubProductSource()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($stubProductId);

        /** @var ProductSource|\PHPUnit_Framework_MockObject_MockObject $stubProductSource */
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getId')->willReturn($stubProductId);
        $stubProductSource->method('getProductForContext')->willReturn($stubProduct);

        return $stubProductSource;
    }
}
