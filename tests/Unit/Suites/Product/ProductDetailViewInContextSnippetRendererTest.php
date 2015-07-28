<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\Snippet;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\TestFileFixtureTrait;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductDetailViewInContextSnippetRenderer
 * @uses   \Brera\Snippet
 * @uses   \Brera\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductDetailViewInContextSnippetRenderer
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

        $this->renderer = new ProductDetailViewInContextSnippetRenderer(
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
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);
    }

    public function testContainedJson()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);

        $method = new \ReflectionMethod($this->renderer, 'getProductDetailPageMetaSnippet');
        $method->setAccessible(true);
        /** @var Snippet $result */
        $result = $method->invoke($this->renderer);
        $this->assertInternalType('array', json_decode($result->getContent(), true));
    }

    public function testContextPartsFetchingIsDelegatedToKeyGenerator()
    {
        $dummyContextParts = ['foo', 'bar', 'baz'];
        $this->mockProductDetailViewSnippetKeyGenerator->method('getContextPartsUsedForKey')->willReturn($dummyContextParts);

        $this->assertSame($dummyContextParts, $this->renderer->getUsedContextParts());
    }
}
