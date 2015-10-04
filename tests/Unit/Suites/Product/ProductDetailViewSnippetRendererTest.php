<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductDetailViewSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductDetailViewSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductDetailPageMetaSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $stubProductDetailViewBlockRenderer = $this->getMock(ProductDetailViewBlockRenderer::class, [], [], '', false);
        $stubProductDetailViewBlockRenderer->method('render')->willReturn('dummy content');
        $stubProductDetailViewBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubProductDetailViewBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->mockProductDetailViewSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockProductDetailViewSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->mockProductDetailPageMetaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockProductDetailPageMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-url-key');

        $this->renderer = new ProductDetailViewSnippetRenderer(
            $this->mockSnippetList,
            $stubProductDetailViewBlockRenderer,
            $this->mockProductDetailViewSnippetKeyGenerator,
            $this->mockProductDetailPageMetaSnippetKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testProductDetailViewSnippetsAreRendered()
    {
        $this->mockSnippetList->expects($this->exactly(2))->method('add');
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getId')->willReturn(2);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        $this->renderer->render($stubProduct);
    }

    public function testContainedJson()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getId')->willReturn(2);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        $this->renderer->render($stubProduct);

        $method = new \ReflectionMethod($this->renderer, 'getProductDetailPageMetaSnippet');
        $method->setAccessible(true);
        /** @var Snippet $result */
        $result = $method->invoke($this->renderer);
        $this->assertInternalType('array', json_decode($result->getContent(), true));
    }

    public function testContextPartsFetchingIsDelegatedToKeyGenerator()
    {
        $dummyContextParts = ['foo', 'bar', 'baz'];
        $this->mockProductDetailViewSnippetKeyGenerator->method('getContextPartsUsedForKey')
            ->willReturn($dummyContextParts);

        $this->assertSame($dummyContextParts, $this->renderer->getUsedContextParts());
    }
}
