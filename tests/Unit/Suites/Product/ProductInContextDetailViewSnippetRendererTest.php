<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetResultList;
use Brera\TestFileFixtureTrait;

class ProductInContextDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductDetailViewBlockRenderer
     */
    private $stubProductDetailViewBlockRenderer;

    /**
     * @var ProductDetailViewSnippetKeyGenerator
     */
    private $stubProductDetailViewSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->stubProductDetailViewBlockRenderer = $this->getMockBuilder(ProductDetailViewBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn($this->getMock(SnippetResultList::class));
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);
        $this->stubProductDetailViewSnippetKeyGenerator = $this->getMock(ProductDetailViewSnippetKeyGenerator::class);
        $this->stubProductDetailViewSnippetKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInContext')
            ->willReturn('stub-key');
        $this->renderer = new ProductInContextDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->stubProductDetailViewBlockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductDetailViewSnippets()
    {
        $this->mockSnippetResultList->expects($this->once())->method('merge');
        $this->mockSnippetResultList->expects($this->once())->method('add');
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);
    }
}
