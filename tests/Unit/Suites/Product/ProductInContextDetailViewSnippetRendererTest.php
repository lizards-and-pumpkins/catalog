<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetResult;
use Brera\SnippetResultList;
use Brera\TestFileFixtureTrait;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductInContextDetailViewSnippetRenderer
 * @uses   \Brera\SnippetResult
 * @uses   \Brera\PageMetaInfoSnippetContent
 */
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
     * @var ProductDetailViewBlockRenderer||\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductDetailViewBlockRenderer;

    /**
     * @var ProductDetailViewSnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductDetailViewSnippetKeyGenerator;

    /**
     * @var UrlPathKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlPathKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->stubProductDetailViewBlockRenderer = $this->getMockBuilder(ProductDetailViewBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn('dummy content');
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('getRootSnippetCode')
            ->willReturn('dummy root block code');
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);
        $this->mockProductDetailViewSnippetKeyGenerator = $this->getMock(ProductDetailViewSnippetKeyGenerator::class);
        $this->mockProductDetailViewSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');
        $this->stubUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $this->stubUrlPathKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInContext')
            ->willReturn('stub-url-key');
        $this->renderer = new ProductInContextDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->stubProductDetailViewBlockRenderer,
            $this->mockProductDetailViewSnippetKeyGenerator,
            $this->stubUrlPathKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductDetailViewSnippets()
    {
        $this->mockSnippetResultList->expects($this->exactly(2))->method('add');
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);
    }

    /**
     * @test
     */
    public function itShouldContainJson()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())->method('getId')->willReturn(2);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);

        $method = new \ReflectionMethod($this->renderer, 'getProductDetailPageMetaSnippet');
        $method->setAccessible(true);
        /** @var SnippetResult $result */
        $result = $method->invoke($this->renderer);
        $this->assertInternalType('array', json_decode($result->getContent(), true));
    }
}
