<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetResultList;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\ProductInContextInListingSnippetRenderer
 * @uses   \Brera\SnippetResult
 */
class ProductInContextInListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductInContextDetailViewSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var ProductInListingBlockRenderer||\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductInListingBlockRenderer;

    /**
     * @var ProductSnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductDetailViewSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->stubProductInListingBlockRenderer = $this->getMockBuilder(ProductInListingBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn('dummy content');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getRootSnippetCode')
            ->willReturn('dummy root block code');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);

        $this->mockProductDetailViewSnippetKeyGenerator = $this->getMock(ProductSnippetKeyGenerator::class);
        $this->mockProductDetailViewSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');

        $this->renderer = new ProductInContextInListingSnippetRenderer(
            $this->mockSnippetResultList,
            $this->stubProductInListingBlockRenderer,
            $this->mockProductDetailViewSnippetKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductDetailViewSnippets()
    {
        $this->mockSnippetResultList->expects($this->once())
            ->method('add');

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->renderer->render($stubProduct, $stubContext);
    }
}
